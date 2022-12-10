<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Psr\Tracing;

use Throwable;
use LogicException;
use ReflectionMethod;

use Relay\Relay;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\API\Common\Instrumentation\Globals;

class RelayOpenTelemetry
{
    /**
     * The Relay instance.
     *
     * @var \Relay\Relay
     */
    protected Relay $relay;

    /**
     * The OpenTelemetry tracer provider instance.
     *
     * @var \OpenTelemetry\API\Trace\TracerInterface
     */
    protected TracerInterface $tracer;

    /**
     * Whether the method should be traced.
     *
     * @var array<string, bool>
     */
    protected array $traced = [];

    /**
     * Whether the method should be traced.
     *
     * @var array<string, bool>
     */
    protected array $command = [];

    /**
     * Creates a new instance.
     *
     * @param  callable  $client
     * @param  ?\OpenTelemetry\API\Trace\TracerProviderInterface  $tracerProvider
     * @return void
     */
    public function __construct(callable $client, ?TracerProviderInterface $tracerProvider = null)
    {
        if (! $tracerProvider) {
            $tracerProvider = Globals::tracerProvider();
        }

        $this->tracer = $tracerProvider->getTracer('Relay', Relay::Version);

        $span = $this->tracer->spanBuilder('Relay::__construct')
            ->setAttribute('db.system', 'redis')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        try {
            $relay = $client();
        } catch (Throwable $exception) {
            $span->recordException($exception);

            throw $exception;
        } finally {
            $span->end();
        }

        if (! $relay instanceof Relay) {
            throw new LogicException('Client is not a Relay instance');
        }

        $this->relay = $relay;
    }

    /**
     * Executes Relay methods inside OpenTelemetry span.
     *
     * @param  string  $name
     * @param  array<mixed>  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (! isset($this->traced[$name])) {
            $this->setMethodAttributes($name);
        }

        if (! $this->traced[$name]) {
            return $this->relay->{$name}(...$arguments);
        }

        $span = $this->tracer->spanBuilder('Relay::' . strtolower($name))
            ->setAttribute('db.system', 'redis')
            ->setAttribute('db.operation', $this->command[$name]
                ? strtoupper($name)
                : null
            )
            ->setAttribute('db.statement', $this->command[$name]
                ? sprintf('%s %s', $this->command[$name], implode(' ', $arguments))
                : null
            )
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        try {
            return $this->relay->{$name}(...$arguments);
        } catch (Throwable $exception) {
            $span->recordException($exception);

            throw $exception;
        } finally {
            $span->end();
        }
    }

    /**
     * Executes static Relay methods.
     *
     * @param  string  $name
     * @param  array<mixed>  $arguments
     * @return void
     */
    public static function __callStatic(string $name, array $arguments)
    {
        throw new LogicException('Unable to trace calls to static methods');
    }

    /**
     * Scan the keyspace for matching keys inside OpenTelemetry span.
     *
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @param  ?string  $type
     * @return array<mixed>|false
     */
    public function scan(&$iterator, $match = null, int $count = 0, ?string $type = null)
    {
        $span = $this->tracer->spanBuilder('Relay::scan')
            ->setAttribute('db.operation', 'SCAN')
            ->setAttribute('db.statement', 'SCAN ' . implode(" ", array_map('json_encode', func_get_args())))
            ->setAttribute('db.system', 'redis')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        try {
            return $this->relay->scan($iterator, $match, $count, $type);
        } catch (Throwable $exception) {
            $span->recordException($exception);

            throw $exception;
        } finally {
            $span->end();
        }
    }

    /**
     * Iterates fields of Hash types inside OpenTelemetry span.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<mixed>|false
     */
    public function hscan($key, &$iterator, $match = null, int $count = 0)
    {
        $span = $this->tracer->spanBuilder('Relay::hscan')
            ->setAttribute('db.operation', 'HSCAN')
            ->setAttribute('db.statement', 'HSCAN ' . implode(" ", array_map('json_encode', func_get_args())))
            ->setAttribute('db.system', 'redis')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        try {
            return $this->relay->hscan($key, $iterator, $match, $count);
        } catch (Throwable $exception) {
            $span->recordException($exception);

            throw $exception;
        } finally {
            $span->end();
        }
    }

    /**
     * Iterates elements of Sets types inside OpenTelemetry span.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<mixed>|false
     */
    public function sscan($key, &$iterator, $match = null, int $count = 0)
    {
        $span = $this->tracer->spanBuilder('Relay::sscan')
            ->setAttribute('db.operation', 'SSCAN')
            ->setAttribute('db.statement', 'SSCAN ' . implode(" ", array_map('json_encode', func_get_args())))
            ->setAttribute('db.system', 'redis')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        try {
            return $this->relay->sscan($key, $iterator, $match, $count);
        } catch (Throwable $exception) {
            $span->recordException($exception);

            throw $exception;
        } finally {
            $span->end();
        }
    }

    /**
     * Iterates elements of Sorted Set types inside OpenTelemetry span.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array<mixed>|false
     */
    public function zscan($key, &$iterator, $match = null, int $count = 0)
    {
        $span = $this->tracer->spanBuilder('Relay::zscan')
            ->setAttribute('db.operation', 'ZSCAN')
            ->setAttribute('db.statement', 'ZSCAN ' . implode(" ", array_map('json_encode', func_get_args())))
            ->setAttribute('db.system', 'redis')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        try {
            return $this->relay->zscan($key, $iterator, $match, $count);
        } catch (Throwable $exception) {
            $span->recordException($exception);

            throw $exception;
        } finally {
            $span->end();
        }
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

        $span = $this->tracer->spanBuilder('Relay::exec')
            ->setAttribute('db.operation', 'EXEC')
            ->setAttribute('db.system', 'redis')
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        try {
            $pipe = $this->relay->{$method}();

            foreach ($transaction->commands as $command) {
                $pipe->{$command[0]}(...$command[1]);
            }

            return $pipe->exec();
        } catch (Throwable $exception) {
            $span->recordException($exception);

            throw $exception;
        } finally {
            $span->end();
        }
    }

    /**
     * Set the method's attributes.
     *
     * @param  string  $name
     * @return void
     */
    protected function setMethodAttributes(string $name)
    {
        $method = new ReflectionMethod($this->relay::class, $name);

        $attributes = array_flip(array_map(
            fn ($attribute) => $attribute->getName(),
            $method->getAttributes()
        ));

        $this->command[$name] = isset($attributes['Relay\Attributes\RedisCommand']);

        $this->traced[$name] = $this->command[$name]
            || isset($attributes['Relay\Attributes\Server']);
    }
}
