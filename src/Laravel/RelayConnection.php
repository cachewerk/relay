<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Laravel;

use Relay\Relay;

use Illuminate\Contracts\Redis\Connection;
use Illuminate\Redis\Connections\PhpRedisConnection;

use CacheWerk\Relay\Laravel\Concerns\InteractsWithRelay;

/**
 * @mixin Relay
 */
class RelayConnection extends PhpRedisConnection implements Connection
{
    use InteractsWithRelay;

    /**
     * The Redis client.
     *
     * @var Relay
     */
    protected $client;

    /**
     * Returns a unique representation of the underlying socket connection identifier.
     *
     * @return string|false
     */
    public function socketId()
    {
        return $this->client->socketId();
    }

    /**
     * Returns information about the license.
     *
     * @return array<string, mixed>
     */
    public function license()
    {
        return $this->client->license();
    }
}
