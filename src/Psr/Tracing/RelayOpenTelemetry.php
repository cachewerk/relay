<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Psr\Tracing;

use Relay\Relay;

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\TracerInterface;

class RelayOpenTelemetry
{
    /**
     * The Relay instance.
     *
     * @var \Relay\Relay
     */
    protected Relay $relay;

    /**
     * The OpenTelemetry tracer instance.
     *
     * @var \OpenTelemetry\API\Trace\TracerInterface
     */
    protected TracerInterface $tracer;

    /**
     * Creates a new `NewRelicRelay` instance.
     *
     * @param  \Relay\Relay  $relay
     * @param  \OpenTelemetry\API\Trace\TracerInterface  $tracer
     * @return void
     */
    public function __construct(Relay $relay, TracerInterface $tracer)
    {
        $this->relay = new $relay;
        $this->tracer = new $tracer;
    }

    /**
     * Executes Relay methods inside OpenTelemetry span.
     *
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $span = $this->tracer->spanBuilder('Relay') // TODO: What's the name?
            ->setAttribute('command', strtolower($name))
            ->setSpanKind(SpanKind::KIND_CLIENT) // TODO: Is this the correct kind?
            ->startSpan()
            ->activate(); // TODO: Is this needed?

        $result = $this->relay->{$name}(...$arguments);

        $span->end();

        return $result;
    }

    /**
     * Executes static Relay methods inside OpenTelemetry span.
     *
     * @param  string  $name
     * @param  array  $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        //
    }

    /**
     * Scan the keyspace for matching keys inside OpenTelemetry span.
     *
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @param  ?string  $type
     * @return array|false
     */
    public function scan(&$iterator, $match = null, int $count = 0, ?string $type = null)
    {
        //
    }

    /**
     * Iterates fields of Hash types inside OpenTelemetry span.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array|false
     */
    public function hscan($key, &$iterator, $match = null, int $count = 0)
    {
        //
    }

    /**
     * Iterates elements of Sets types inside OpenTelemetry span.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array|false
     */
    public function sscan($key, &$iterator, $match = null, int $count = 0)
    {
        //
    }

    /**
     * Iterates elements of Sorted Set types inside OpenTelemetry span.
     *
     * @param  mixed  $key
     * @param  mixed  $iterator
     * @param  mixed  $match
     * @param  int  $count
     * @return array|false
     */
    public function zscan($key, &$iterator, $match = null, int $count = 0)
    {
        //
    }
}
