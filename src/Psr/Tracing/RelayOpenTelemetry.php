<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Psr\Tracing;

use Throwable;
use LogicException;

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
     * Maximum string length when formatting commands for OpenTelemetry.
     *
     * @var int
     */
    protected const MAX_STR_LEN = 100;

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

        $this->tracer = $tracerProvider->getTracer('Relay', (string) phpversion('relay'));

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
        if (in_array($name, self::Untraced)) {
            return $this->relay->{$name}(...$arguments);
        }

        $operation = strtoupper($name);
        $stmt = $this->fmtCommand($operation, $arguments);

        $span = $this->tracer->spanBuilder('Relay::' . strtolower($name))
            ->setAttribute('db.system', 'redis')
            ->setAttribute('db.operation', $operation)
            ->setAttribute('db.statement', $stmt)
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
        $stmt = $this->fmtScan('SCAN', null, $iterator, $match, $count, $type);
        $span = $this->tracer->spanBuilder('Relay::scan')
            ->setAttribute('db.system', 'redis')
            ->setAttribute('db.operation', 'SCAN')
            ->setAttribute('db.statement', $stmt)
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
        $stmt = $this->fmtScan('HSCAN', $key, $iterator, $match, $count);
        $span = $this->tracer->spanBuilder('Relay::hscan')
            ->setAttribute('db.system', 'redis')
            ->setAttribute('db.operation', 'HSCAN')
            ->setAttribute('db.statement', $stmt)
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
        $stmt = $this->fmtScan('SSCAN', $key, $iterator, $match, $count);
        $span = $this->tracer->spanBuilder('Relay::sscan')
            ->setAttribute('db.system', 'redis')
            ->setAttribute('db.operation', 'SSCAN')
            ->setAttribute('db.statement', $stmt)
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
        $stmt = $this->fmtScan('ZSCAN', $key, $iterator, $match, $count);
        $span = $this->tracer->spanBuilder('Relay::zscan')
            ->setAttribute('db.system', 'redis')
            ->setAttribute('db.operation', 'ZSCAN')
            ->setAttribute('db.statement', $stmt)
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

        $stmt = $this->fmtCommands($transaction->commands);
        $span = $this->tracer->spanBuilder('Relay::' . $method)
            ->setAttribute('db.system', 'redis')
            ->setAttribute('db.operation', $method)
            ->setAttribute('db.statement', $stmt)
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
     * Formats multiple commands for db.statement attribute.
     *
     * @param  array<mixed> $commands
     * @return string
     */
    protected function fmtCommands(array $commands)
    {
        $acc = [];
        $len = 0;
        foreach ($commands as $command) {
            $command = $this->fmtCommand($command[0], $command[1]);
            $len += strlen($command);
            if ($len > 5000) {
                break;
            }
            array_push($acc, $command);
        }
        return implode(PHP_EOL, $acc);
    }

    /**
     * Formats a single command for db.statement attribute.
     *
     * @param  string       $name
     * @param  array<mixed> $arguments
     * @return string
     */
    protected function fmtCommand(string $operation, array $arguments)
    {
        $acc = [$operation];
        $len = 0;
        $this->fmtArguments($acc, $len, $arguments);
        return implode(' ', $acc);
    }

    /**
     * Formats passed arguments as strings.
     *
     * @param  array<string> $acc
     * @param  array<mixed>  $arguments
     * @return void
     */
    protected function fmtArguments(&$acc, int &$len, $arguments)
    {
        foreach ($arguments as $value) {
            if (is_array($value)) {
                $this->fmtArguments($acc, $len, $value);
                continue;
            }

            $str = strval($value);
            if (strlen($str) > self::MAX_STR_LEN) {
                $str = substr($str, 0, self::MAX_STR_LEN - 3) + '...';
            }

            $len += strlen($str);
            if ($len > 1000) {
                array_push($acc, '...');
                break;
            }
            array_push($acc, $str);
        }
    }

    /**
     * Formats SCAN command arguments.
     *
     * @param  string  $name
     * @param  mixed   $key
     * @param  mixed   $iterator
     * @param  mixed   $match
     * @param  int     $count
     * @param  ?string $type
     * @return string
     */
    protected function fmtScan(string $name, $key, $iterator, $match = null, int $count = 0, ?string $type = null)
    {
        $args = [$name];
        if ($key) {
            array_push($args, $key);
        }
        array_push($args, $iterator);
        if ($match) {
            array_push($args, 'MATCH', $match);
        }
        if ($count > 0) {
            array_push($args, 'COUNT', $count);
        }
        if ($type) {
            array_push($args, 'TYPE', $type);
        }
        return implode(' ', $args);
    }
}
