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
     * Create a new Relay connection.
     *
     * @param  array<mixed>  $config
     * @param  array<mixed>  $options
     * @return \CacheWerk\Relay\Laravel\RelayConnection
     */
    public function connect(array $config, array $options)
    {
        $formattedOptions = Arr::pull($config, 'options', []);

        if (isset($config['prefix'])) {
            $formattedOptions['prefix'] = $config['prefix'];
        }

        $connector = function () use ($config, $options, $formattedOptions) {
            return $this->createClient(array_merge(
                $config, $options, $formattedOptions
            ));
        };

        return new RelayConnection($connector(), $connector, $config);
    }

    /**
     * Create a new clustered Relay connection.
     *
     * @param  array<mixed>  $config
     * @param  array<mixed>  $clusterOptions
     * @param  array<mixed>  $options
     * @return \CacheWerk\Relay\Laravel\RelayClusterConnection
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options)
    {
        throw new LogicException('Relay does not support clusters, at this point.');
    }

    /**
     * Create the Relay client instance.
     *
     * @param  array<int>  $config
     * @return \Relay\Relay
     *
     * @throws \LogicException
     */
    protected function createClient(array $config)
    {
        $client = new Relay;

        $this->establishConnection($client, $config);

        $client->setOption(Relay::OPT_PHPREDIS_COMPATIBILITY, true);

        if (! empty($config['password'])) {
            if (isset($config['username']) && $config['username'] !== '' && is_string($config['password'])) {
                $client->auth([$config['username'], $config['password']]);
            } else {
                $client->auth($config['password']);
            }
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

        return $client;
    }
}
