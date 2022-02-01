<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Laravel;

use Illuminate\Contracts\Redis\Connection;
use Illuminate\Redis\Connections\PhpRedisConnection;

/**
 * @mixin \Relay\Relay
 */
class RelayConnection extends PhpRedisConnection implements Connection
{
    /**
     * Dispatch invalidation events.
     *
     * @return int|false
     */
    public function dispatchEvents()
    {
        return $this->client->dispatchEvents();
    }

    /**
     * Registers a Relay event listener.
     *
     * Normally `Relay::listen()` is used for this, but due to the conflict
     * with Laravel's `Connection::listen()` this method acts as an alias.
     *
     * @see \Illuminate\Redis\Connections\Connection::listen()
     *
     * @param  callable  $callback
     * @return bool
     */
    public function listenFor(callable $callback)
    {
        return $this->client->listen($callback);
    }

    /**
     * Registers an event listener for flushes.
     *
     * @param  callable  $callback
     * @return bool
     */
    public function onFlushed(callable $callback)
    {
        return $this->client->onFlushed($callback);
    }

    /**
     * Registers an event listener for invalidations.
     *
     * @param  callable  $callback
     * @param  string  $pattern
     * @return bool
     */
    public function onInvalidated(callable $callback, string $pattern = null)
    {
        return $this->client->onInvalidated($callback, $pattern);
    }

    /**
     * Returns statistics about Relay.
     *
     * @return array
     */
    public function stats()
    {
        return $this->client->stats();
    }

    /**
     * Returns information about the Relay license.
     *
     * @return array
     */
    public function license()
    {
        return $this->client->license();
    }

    /**
     * Returns the connections socket identifier.
     *
     * @return int|false
     */
    public function socketId()
    {
        return $this->client->socketId();
    }

    /**
     * Returns the connections socket key.
     *
     * Bypasses the `command()` method to avoid log spam.
     *
     * @return string|false
     */
    public function socketKey()
    {
        return $this->client->socketKey();
    }
}
