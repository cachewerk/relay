<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Laravel;

use LogicException;

use Relay\Relay;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Redis\Connector;
use Illuminate\Redis\Connectors\PhpRedisConnector;

class RelayConnector extends PhpRedisConnector implements Connector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config, array $options)
    {
        $connector = function () use ($config, $options) {
            return $this->createClient(array_merge(
                $config, $options, Arr::pull($config, 'options', [])
            ));
        };

        return new RelayConnection($connector(), $connector, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options)
    {
        throw new LogicException('Relay does not support clusters, at this point.');
    }

    /**
     * {@inheritdoc}
     */
    protected function createClient(array $config)
    {
        return tap(new Relay, function ($client) use ($config) {
            $this->establishConnection($client, $config);

            $client->setOption(Relay::OPT_PHPREDIS_COMPATIBILITY, true);

            if (! empty($config['password'])) {
                $client->auth($config['password']);
            }

            if (isset($config['database'])) {
                $client->select((int) $config['database']);
            }

            if (! empty($config['prefix'])) {
                $client->setOption(Relay::OPT_PREFIX, $config['prefix']);
            }

            if (! empty($config['read_timeout'])) {
                $client->setOption(Relay::OPT_READ_TIMEOUT, $config['read_timeout']);
            }

            if (! empty($config['scan'])) {
                $client->setOption(Relay::OPT_SCAN, $config['scan']);
            }

            if (! empty($config['name'])) {
                $client->client('SETNAME', $config['name']);
            }

            if (array_key_exists('serializer', $config)) {
                $client->setOption(Relay::OPT_SERIALIZER, $config['serializer']);
            }

            if (array_key_exists('compression', $config)) {
                $client->setOption(Relay::OPT_COMPRESSION, $config['compression']);
            }

            if (array_key_exists('compression_level', $config)) {
                $client->setOption(Relay::OPT_COMPRESSION_LEVEL, $config['compression_level']);
            }
        });
    }
}
