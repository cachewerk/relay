<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Psr\Tracing;

use LogicException;
use ReflectionMethod;

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
     * Whether the method should be traced.
     *
     * @var array<string, bool>
     */
    protected static array $traced = [];

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
        isset(self::$traced[$name]) || self::setMethodTraceability($name);

        if (! self::$traced[$name]) {
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
        isset(self::$traced[$name]) || self::setMethodTraceability($name);

        if (! self::$traced[$name]) {
            return Relay::{$name}(...$arguments);
        }

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
            /** @var \Relay\Relay $pipe */
            $pipe = $this->relay->{$method}();

            foreach ($transaction->commands as $command) {
                $pipe->{$command[0]}(...$command[1]);
            }

            return $pipe->exec();
        }, [
            'product' => 'Redis',
            'operation' => 'exec',
        ]);
    }

    /**
     * Set whether the method should be traced.
     *
     * @param  string  $name
     * @return void
     */
    protected static function setMethodTraceability(string $name)
    {
        $method = new ReflectionMethod(Relay::class, $name);

        $attributes = array_map(
            fn ($attribute) => $attribute->getName(),
            $method->getAttributes()
        );

        $matches = array_diff([
            'Relay\Attributes\Server',
            'Relay\Attributes\RedisCommand',
        ], $attributes);

        self::$traced[$name] = count($matches) != 2;
    }
}
