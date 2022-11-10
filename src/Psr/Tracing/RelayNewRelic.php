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
    protected Relay $relay;

    /**
     * Untraced client methods.
     *
     * @var array<int, string>
     */
    public const Untraced = [
        'listen',
        'onFlushed',
        'onInvalidated',
        'dispatchEvents',
        'endpointId',
        'socketId',
        'idleTime',
        'option',
        'getOption',
        'setOption',
        'readTimeout',
        'getReadTimeout',
        'getHost',
        'getPort',
        'getAuth',
        'getDbNum',
        'getMode',
        'getLastError',
        'clearLastError',
        '_serialize',
        '_unserialize',
        '_pack',
        '_unpack',
        '_prefix',
    ];

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

        $relay = newrelic_record_datastore_segment(
            $client,
            ['product' => 'Redis', 'operation' => '__construct']
        );

        if (! $relay instanceof Relay) {
            throw new LogicException('Client is not a Relay instance');
        }

        $this->relay = $relay;
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
        if (in_array($name, self::Untraced)) {
            return $this->relay->{$name}(...$arguments);
        }

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
            'operation' => 'scan',
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
            'operation' => 'hscan',
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
            'operation' => 'sscan',
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
            'operation' => 'zscan',
        ]);
    }

    /**
     * Hijack pipelines.
     *
     * @return \CacheWerk\Relay\Psr\Tracing\Transaction
     */
    public function pipeline()
    {
        return new Transaction($this, Relay::PIPELINE);
    }

    /**
     * Hijack pipelines.
     *
     * @param  int  $mode
     * @return \CacheWerk\Relay\Psr\Tracing\Transaction
     */
    public function multi(int $mode = Relay::MULTI)
    {
        return new Transaction($this, $mode);
    }

    /**
     * Block non-chained transactions.
     *
     * @return void
     */
    public function exec()
    {
        throw new LogicException('Non-chained transactions are not supported');
    }

    /**
     * Executes buffered transaction inside New Relic's datastore segment function.
     *
     * @phpstan-return mixed
     *
     * @param  \CacheWerk\Relay\Psr\Tracing\Transaction  $transaction
     * @return array<int, mixed>|bool
     */
    public function executeBufferedTransaction(Transaction $transaction)
    {
        $method = $transaction->type === Relay::PIPELINE
            ? 'pipeline'
            : 'multi';

        return newrelic_record_datastore_segment(function () use ($method, $transaction) {
            $pipe = $this->relay->{$method}();

            foreach ($transaction->commands as $command) {
                $pipe->{$command[0]}(...$command[1]);
            }

            return $pipe->exec();
        }, [
            'product' => 'Redis',
            'operation' => $method,
        ]);
    }
}
