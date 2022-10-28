<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Psr\Tracing;

use Relay\Relay;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;

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
     * Creates a new `NewRelicRelay` instance.
     *
     * @param  \Relay\Relay  $relay
     * @param  \OpenTelemetry\API\Trace\TracerProviderInterface  $tracerProvider
     * @return void
     */
    public function __construct(Relay $relay, TracerProviderInterface $tracerProvider)
    {
        $this->relay = $relay;
        $this->tracer = $tracerProvider->getTracer('Relay', (string) phpversion('relay'));
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
        $span = $this->tracer->spanBuilder('Relay::' . strtolower($name))
            ->setAttribute('db.operation', strtoupper($name))
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        $result = $this->relay->{$name}(...$arguments);

        $span->end();

        return $result;
    }

    /**
     * Executes static Relay methods.
     *
     * @param  string  $name
     * @param  array<mixed>  $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        return Relay::{$name}(...$arguments);
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
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        $result = $this->relay->scan($iterator, $match, $count, $type);

        $span->end();

        return $result;
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
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        $result = $this->relay->hscan($key, $iterator, $match, $count);

        $span->end();

        return $result;
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
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        $result = $this->relay->sscan($key, $iterator, $match, $count);

        $span->end();

        return $result;
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
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->startSpan();

        $result = $this->relay->zscan($key, $iterator, $match, $count);

        $span->end();

        return $result;
    }
}
