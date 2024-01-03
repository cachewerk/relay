<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Laravel;

use Illuminate\Contracts\Redis\Connection;

/**
 * @mixin \Relay\Cluster
 */
class RelayClusterConnection extends RelayConnection implements Connection
{
    /**
     * The Redis client.
     *
     * @var \Relay\Relay
     */
    protected $client;

    /**
     * Flush the selected Redis database on all master nodes.
     *
     * @return mixed
     */
    public function flushdb()
    {
        $arguments = func_get_args();

        $async = strtoupper((string) ($arguments[0] ?? null)) === 'ASYNC';

        foreach ($this->client->_masters() as $master) {
            $async
                ? $this->command('rawCommand', [$master, 'flushdb', 'async'])
                : $this->command('flushdb', [$master]);
        }
    }
}
