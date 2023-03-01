<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Session;

use Relay\Relay;
use Relay\Exception;

use SessionIdInterface;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

class RelaySessionHandler implements SessionHandlerInterface, SessionIdInterface, SessionUpdateTimestampHandlerInterface
{
    /**
     * The of seconds after which data will be seen as 'garbage' and cleaned up.
     *
     * @var int
     */
    protected int $ttl;

    /**
     * Session id of prefetched data.
     *
     * @var string
     */
    private ?string $sessionId;

    /**
     * Session data of prefetched data.
     *
     * @var mixed
     */
    private mixed $sessionData;

    /**
     * Creates a new session handler instance.
     *
     * @param  Relay  $relay
     * @param  ?int  $ttl
     * @return void
     */
    public function __construct(protected Relay $relay, ?int $ttl = null)
    {
        $this->ttl = (int) ($ttl ?: ini_get('session.gc_maxlifetime') ?: 1440);
    }

    /**
     * Registers this instance as the current session handler.
     *
     * @return bool
     */
    public function register(): bool
    {
        return session_set_save_handler($this, true);
    }

    /**
     * Ensures Relay is connected.
     *
     * @param  string  $savePath
     * @param  string  $sessionName
     * @return bool
     */
    public function open(string $savePath, string $sessionName): bool
    {
        return $this->relay->isConnected();
    }

    /**
     * Returns session data for the given session id.
     *
     * @param  string  $id
     * @return string|false
     */
    public function read(#[\SensitiveParameter] string $id): string|false
    {
        if ($this->sessionId === $id) {
            $data = $this->sessionData;

            unset($this->sessionId, $this->sessionData);

            return $data;
        }

        try {
            $data = $this->relay->get($id);

            return empty($data)
                ? false
                : $this->unserialize($data);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Writes given data to session id.
     *
     * @param  string  $id
     * @param  string  $data
     * @return bool
     */
    public function write(#[\SensitiveParameter] string $id, string $data): bool
    {
        try {
            return $this->relay->setex($id, $this->ttl, $this->serialize($data));
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Destroys the session for the given session id.
     *
     * @param  string  $id
     * @return bool
     */
    public function destroy(#[\SensitiveParameter] string $id): bool
    {
        try {
            return (bool) $this->relay->del($id);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Creates a session identifier that mimics PHP's native session id format.
     *
     * @return string
     */
    public function create_sid(): string
    {
        return implode('', array_map(
            fn () => base_convert((string) random_int(0, 36), 10, 36),
            array_fill(0, 26, 42)
        ));
    }

    /**
     * Validates the given session id.
     *
     * @param  string  $id
     * @return bool
     */
    public function validateId(#[\SensitiveParameter] string $id): bool
    {
        try {
            $this->sessionId = $id;
            $this->sessionData = $this->unserialize($this->relay->get($id));
        } catch (Exception) {
            return false;
        }

        return $this->sessionData !== false;
    }

    /**
     * Resets the session lifetime of the session to the TTL.
     *
     * @param  string  $id
     * @param  string  $data
     * @return bool
     */
    public function updateTimestamp(string $id, string $data): bool
    {
        try {
            return $this->relay->expire($id, (int) $this->ttl);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * NOOP.
     *
     * @param  int  $max_lifetime
     * @return int|false
     */
    public function gc(int $max_lifetime): int|false
    {
        return false;
    }

    /**
     * NOOP.
     *
     * @return bool
     */
    public function close(): bool
    {
        return false;
    }

    /**
     * Serialize given data, if Relay has no serializer configured.
     *
     * @param  mixed  $data
     * @return mixed
     */
    protected function serialize(mixed $data)
    {
        if ($this->relay->getOption(Relay::OPT_SERIALIZER) === Relay::SERIALIZER_NONE) {
            return serialize($data);
        }

        return $data;
    }

    /**
     * Unserialize given data, if Relay has no serializer configured.
     *
     * @param  mixed  $data
     * @return mixed
     */
    protected function unserialize(mixed $data)
    {
        if ($this->relay->getOption(Relay::OPT_SERIALIZER) === Relay::SERIALIZER_NONE) {
            return unserialize($data);
        }

        return $data;
    }
}
