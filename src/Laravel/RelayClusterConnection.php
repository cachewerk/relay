<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Laravel;

use InvalidArgumentException;

use Relay\Cluster;

use Illuminate\Contracts\Redis\Connection;

/**
 * @mixin Cluster
 */
class RelayClusterConnection extends RelayConnection implements Connection
{
    /**
     * The Redis client.
     *
     * @var Cluster
     */
    protected $client;

    /**
     * The default node to use from the cluster.
     *
     * @var string|array<mixed>|null
     */
    protected $defaultNode = null;

    /**
     * Scan all keys based on the given options.
     *
     * @param  mixed  $cursor
     * @param  array<string, mixed>  $options
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function scan($cursor, $options = [])
    {
        /** @var string|array<mixed> $node */
        $node = $options['node'] ?? $this->defaultNode();

        /** @var int $count */
        $count = $options['count'] ?? 10;

        $result = $this->client->scan($cursor,
            $node,
            $options['match'] ?? '*',
            $count
        );

        if ($result === false) {
            $result = [];
        }

        return $cursor === 0 && empty($result) ? false : [$cursor, $result];
    }

    /**
     * Flush the selected Redis database on all master nodes.
     *
     * @return void
     */
    public function flushdb()
    {
        /** @var string[] $arguments */
        $arguments = func_get_args();

        $async = strtoupper((string) ($arguments[0] ?? null)) === 'ASYNC';

        foreach ($this->client->_masters() as $master) {
            $async
                ? $this->command('rawCommand', [$master, 'flushdb', 'async'])
                : $this->command('flushdb', [$master]);
        }
    }

    /**
     * Return default node to use for cluster.
     *
     * @return string|array<mixed>
     *
     * @throws InvalidArgumentException
     */
    private function defaultNode()
    {
        if (! isset($this->defaultNode)) {
            $this->defaultNode = $this->client->_masters()[0] ?? throw new InvalidArgumentException(
                'Unable to determine default node. No master nodes found in the cluster.'
            );
        }

        return $this->defaultNode;
    }
}
