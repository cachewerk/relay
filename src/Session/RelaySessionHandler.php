<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Session;

use Relay\Relay;

use SessionIdInterface;
use SessionHandlerInterface;

class RelaySessionHandler implements SessionHandlerInterface, SessionIdInterface
{
    /**
     * The of seconds after which data will be seen as 'garbage' and cleaned up.
     *
     * @var int
     */
    protected int $ttl;

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
     * Creates a session identifier.
     *
     * @return string
     */
    public function create_sid(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(13));
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
    public function read(string $id): string|false
    {
        return $this->relay->get($id) ?: false;
    }

    /**
     * Writes given data to session id.
     *
     * @param  string  $id
     * @param  string  $data
     * @return bool
     */
    public function write(string $id, string $data): bool
    {
        return $this->relay->setex($id, $this->ttl, $data);
    }

    /**
     * Destroys the session.
     *
     * @param  string  $id
     * @return bool
     */
    public function destroy(string $id): bool
    {
        return (bool) $this->relay->unlink($id);
    }

    public function gc(int $max_lifetime): int|false
    {
        return false; // NOOP
    }

    public function close(): bool
    {
        return false; // NOOP
    }
}
