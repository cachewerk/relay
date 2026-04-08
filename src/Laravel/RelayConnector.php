<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Laravel;

use InvalidArgumentException;

use Relay\Relay;
use Relay\Cluster;

use Illuminate\Support\Arr;
use Illuminate\Contracts\Redis\Connector;
use Illuminate\Redis\Connectors\PhpRedisConnector;

class RelayConnector extends PhpRedisConnector implements Connector
{
    /**
     * Create a new Relay connection.
     *
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $options
     * @return RelayConnection
     */
    public function connect(array $config, array $options)
    {
        /** @var array<string, mixed> $formattedOptions */
        $formattedOptions = Arr::pull($config, 'options', []);

        if (isset($config['prefix'])) {
            $formattedOptions['prefix'] = $config['prefix'];
        }

        $connector = function () use ($config, $options, $formattedOptions) {
            return $this->createClient(array_merge(
                $config, $options, (array) $formattedOptions
            ));
        };

        return new RelayConnection($connector(), $connector, $config);
    }

    /**
     * Create a new clustered Relay connection.
     *
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $clusterOptions
     * @param  array<string, mixed>  $options
     * @return RelayClusterConnection
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options)
    {
        $options = array_merge($options, $clusterOptions, (array) Arr::pull($config, 'options', []));

        return new RelayClusterConnection($this->createRedisClusterInstance(
            array_map([$this, 'buildClusterConnectionString'], $config), $options
        ));
    }

    /**
     * Build a single cluster seed string from an array.
     *
     * @param  array<string, string>  $server
     * @return string
     */
    protected function buildClusterConnectionString(array $server)
    {
        return sprintf('%s:%d', $this->formatHost($server), $server['port']);
    }

    /**
     * Create the Relay client instance.
     *
     * @param  array<int>  $config
     * @return Relay
     */
    protected function createClient(array $config)
    {
        $client = new Relay;

        $this->establishConnection($client, $config);

        $client->setOption(Relay::OPT_PHPREDIS_COMPATIBILITY, true);

        if (array_key_exists('max_retries', $config)) {
            $client->setOption(Relay::OPT_MAX_RETRIES, $config['max_retries']);
        }

        if (array_key_exists('backoff_algorithm', $config)) {
            $client->setOption(Relay::OPT_BACKOFF_ALGORITHM, $this->parseBackoffAlgorithm($config['backoff_algorithm']));
        }

        if (array_key_exists('backoff_base', $config)) {
            $client->setOption(Relay::OPT_BACKOFF_BASE, $config['backoff_base']);
        }

        if (array_key_exists('backoff_cap', $config)) {
            $client->setOption(Relay::OPT_BACKOFF_CAP, $config['backoff_cap']);
        }

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

        if (! empty($config['tcp_keepalive'])) {
            $client->setOption(Relay::OPT_TCP_KEEPALIVE, $config['tcp_keepalive']);
        }

        if (defined('Relay\Relay::OPT_PACK_IGNORE_NUMBERS') && array_key_exists('pack_ignore_numbers', $config)) {
            $client->setOption(Relay::OPT_PACK_IGNORE_NUMBERS, $config['pack_ignore_numbers']);
        }

        return $client;
    }

    /**
     * Establish a connection with the Redis host.
     *
     * @param  Relay  $client
     * @param  array<string, mixed>  $config
     * @return void
     */
    protected function establishConnection($client, array $config)
    {
        $persistent = $config['persistent'] ?? false;

        $parameters = [
            $this->formatHost($config),
            is_scalar($config['port']) ? (int) $config['port'] : 0,
            Arr::get($config, 'timeout', 0.0),
            $persistent ? Arr::get($config, 'persistent_id', null) : null,
            Arr::get($config, 'retry_interval', 0),
        ];

        $parameters[] = Arr::get($config, 'read_timeout', 0.0);

        if (! is_null($context = Arr::get($config, 'context'))) {
            $parameters[] = $context;
        }

        $client->{$persistent ? 'pconnect' : 'connect'}(...$parameters); // @phpstan-ignore-line
    }

    /**
     * Create a new Relay cluster instance.
     *
     * @param  array<int, string>  $servers
     * @param  array<string, mixed>  $options
     * @return Cluster
     */
    protected function createRedisClusterInstance(array $servers, array $options)
    {
        $parameters = [
            null,
            array_values($servers),
            $options['timeout'] ?? 0,
            $options['read_timeout'] ?? 0,
            isset($options['persistent']) && $options['persistent'],
        ];

        $parameters[] = $options['password'] ?? null;

        if (! is_null($context = Arr::get($options, 'context'))) {
            $parameters[] = $context;
        }

        $client = new Cluster(...$parameters); // @phpstan-ignore-line

        if (! empty($options['prefix'])) {
            $client->setOption(Relay::OPT_PREFIX, $options['prefix']);
        }

        if (! empty($options['scan'])) {
            $client->setOption(Relay::OPT_SCAN, $options['scan']);
        }

        if (! empty($options['failover'])) {
            $client->setOption(Cluster::OPT_SLAVE_FAILOVER, $options['failover']);
        }

        if (array_key_exists('serializer', $options)) {
            $client->setOption(Relay::OPT_SERIALIZER, $options['serializer']);
        }

        if (array_key_exists('compression', $options)) {
            $client->setOption(Relay::OPT_COMPRESSION, $options['compression']);
        }

        if (array_key_exists('compression_level', $options)) {
            $client->setOption(Relay::OPT_COMPRESSION_LEVEL, $options['compression_level']);
        }

        if (! empty($options['tcp_keepalive'])) {
            $client->setOption(Relay::OPT_TCP_KEEPALIVE, $options['tcp_keepalive']);
        }

        return $client;
    }

    /**
     * Parse a "friendly" backoff algorithm name into an integer.
     *
     * @param  int|string  $algorithm
     * @return int
     *
     * @throws InvalidArgumentException
     */
    protected function parseBackoffAlgorithm($algorithm)
    {
        if (is_int($algorithm)) {
            return $algorithm;
        }

        $algorithms = [
            'default' => Relay::BACKOFF_ALGORITHM_DEFAULT,
            'decorrelated_jitter' => Relay::BACKOFF_ALGORITHM_DECORRELATED_JITTER,
            'equal_jitter' => Relay::BACKOFF_ALGORITHM_EQUAL_JITTER,
            'exponential' => Relay::BACKOFF_ALGORITHM_EXPONENTIAL,
            'uniform' => Relay::BACKOFF_ALGORITHM_UNIFORM,
            'constant' => Relay::BACKOFF_ALGORITHM_CONSTANT,
        ];

        if (! isset($algorithms[$algorithm])) {
            throw new InvalidArgumentException("Algorithm [{$algorithm}] is not a valid backoff algorithm.");
        }

        return $algorithms[$algorithm];
    }
}
