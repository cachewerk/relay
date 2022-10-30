<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Psr\Tracing;

use LogicException;

use Relay\Relay;

use function newrelic_record_datastore_segment;

class RelayNewRelic
{
    /**
     * The Relay instance.
     *
     * @var \Relay\Relay
     */
    protected $relay;

    /**
     * Creates a new instance.
     *
     * @param  callable  $client
     * @return void
     */
    public function __construct(callable $client)
    {
        if (! function_exists('newrelic_record_datastore_segment')) {
            throw new LogicException('Function `newrelic_record_datastore_segment()` was not found');
        }

        $this->relay = newrelic_record_datastore_segment($client, [
            'product' => 'Redis',
            'operation' => '__construct'
        ]);
    }

    /**
     * Executes Relay methods inside New Relic datastore segment function.
     *
     * @param  string  $name
     * @param  array<mixed>  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        return newrelic_record_datastore_segment(
            fn () => $this->relay->{$name}(...$arguments),
            ['product' => 'Redis', 'operation' => $name]
        );
    }

    /**
     * Executes static Relay methods inside New Relic datastore segment function.
     *
     * @param  string  $name
     * @param  array<mixed>  $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return newrelic_record_datastore_segment(
            fn () => Relay::{$name}(...$arguments),
            ['product' => 'Redis', 'operation' => strtolower($name)]
        );
    }

    /**
     * Scan the keyspace for matching keys inside New Relic's datastore segment function.
     *
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @param  ?string  $type
     * @return array<mixed>|false
     */
    public function scan(&$iterator, $match = null, int $count = 0, ?string $type = null)
    {
        return newrelic_record_datastore_segment(function () use (&$iterator, $match, $count, $type) {
            return $this->relay->scan($iterator, $match, $count, $type);
        }, [
            'product' => 'Redis',
            'operation' => 'scan'
        ]);
    }

    /**
     * Iterates fields of Hash types inside New Relic's datastore segment function.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<mixed>|false
     */
    public function hscan($key, &$iterator, $match = null, int $count = 0)
    {
        return newrelic_record_datastore_segment(function () use ($key, &$iterator, $match, $count) {
            return $this->relay->hscan($key, $iterator, $match, $count);
        }, [
            'product' => 'Redis',
            'operation' => 'hscan'
        ]);
    }

    /**
     * Iterates elements of Sets types inside New Relic's datastore segment function.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<mixed>|false
     */
    public function sscan($key, &$iterator, $match = null, int $count = 0)
    {
        return newrelic_record_datastore_segment(function () use ($key, &$iterator, $match, $count) {
            return $this->relay->sscan($key, $iterator, $match, $count);
        }, [
            'product' => 'Redis',
            'operation' => 'sscan'
        ]);
    }

    /**
     * Iterates elements of Sorted Set types inside New Relic's datastore segment function.
     *
     * @param  mixed  $key
     * @param  mixed  &$iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<mixed>|false
     */
    public function zscan($key, &$iterator, $match = null, int $count = 0)
    {
        return newrelic_record_datastore_segment(function () use ($key, &$iterator, $match, $count) {
            return $this->relay->zscan($key, $iterator, $match, $count);
        }, [
            'product' => 'Redis',
            'operation' => 'zscan'
        ]);
    }
}
