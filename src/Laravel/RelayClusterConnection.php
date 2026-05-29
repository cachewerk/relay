<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Laravel;

use Relay\Cluster;

use Illuminate\Contracts\Redis\Connection;
use Illuminate\Redis\Connections\PhpRedisClusterConnection;

use CacheWerk\Relay\Laravel\Concerns\InteractsWithRelay;

/**
 * @mixin Cluster
 */
class RelayClusterConnection extends PhpRedisClusterConnection implements Connection
{
    use InteractsWithRelay;

    /**
     * The Redis client.
     *
     * @var Cluster
     */
    protected $client;

    /**
     * Determine if the connection is to a Redis Cluster.
     *
     * @return bool
     */
    public function isCluster()
    {
        return true;
    }
}
