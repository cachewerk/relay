<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Session;

use Relay\Relay;
use Relay\Exception;

use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

class RelaySessionHandler implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    /**
     * The Relay instance.
     *
     * @var Relay
     */
    protected Relay $relay;

    /**
     * The of seconds after which data will be seen as 'garbage' and cleaned up.
     *
     * @var int
     */
    protected int $ttl;

    /**
     * Session id of prefetched data.
     *
     * @var ?string
     */
    protected ?string $sessionId;

    /**
     * Session data of prefetched data.
     *
     * @var ?string
     */
    protected ?string $sessionData;

    /**
     * Creates a new session handler instance.
     *
     * @param  Relay  $relay
     * @param  ?int  $ttl
     * @return void
     */
    public function __construct(Relay $relay, ?int $ttl = null)
    {
        $this->relay = $relay;
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
    #[\ReturnTypeWillChange]
    public function read(#[\SensitiveParameter] string $id): string|false
    {
        if (isset($this->sessionId, $this->sessionData) && $this->sessionId === $id) {
            $data = $this->sessionData;

            $this->sessionId = null;
            $this->sessionData = null;

            return $data;
        }

        try {
            $data = $this->relay->get($id);

            return is_string($data) ? $data : '';
        } catch (Exception $exception) {
            return '';
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
            return $this->relay->setex($id, $this->ttl, $data);
        } catch (Exception $exception) {
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
        } catch (Exception $exception) {
            return false;
        }
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
            $data = $this->relay->get($id);
        } catch (Exception $exception) {
            return false;
        } finally {
            $this->sessionId = $id;
            $this->sessionData = is_string($data ?? null) ? $data : '';
        }

        return true;
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
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * NOOP.
     *
     * @param  int  $max_lifetime
     * @return int|false
     */
    #[\ReturnTypeWillChange]
    public function gc(int $max_lifetime)
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
}
