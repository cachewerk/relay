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
     * The Redis client.
     *
     * @var \Relay\Relay
     */
    protected $client;

    /**
     * Pass method calls to Relay, or the underlying client.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->client, $method)) {
            return $this->client->{$method}(...$parameters);
        }

        return parent::__call($method, $parameters);
    }
}
