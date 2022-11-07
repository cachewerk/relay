<?php

declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use \CacheWerk\Relay\Psr\Tracing\RelayOpenTelemetry;

$conf = new Uptrace\Config();
$conf->setDsn('http://project2_secret_token@localhost:14318/2');
$conf->setServiceName('myservice');
$conf->setServiceVersion('1.0.0');

$uptrace = new Uptrace\Distro($conf);
$tracerProvider = $uptrace->createTracerProvider();
$tracer = $tracerProvider->getTracer('relay/example-opentelemetry');

$redis = new RelayOpenTelemetry(function() {
    return new Relay\Relay;
}, $tracerProvider);

$redis->connect('127.0.0.1', 6379);

$span = handleRequest($tracer, $redis);
echo $uptrace->traceUrl($span) . PHP_EOL;

for ($i = 0; $i <= 1000000; $i++) {
    handleRequest($tracer, $redis);
    sleep(1);
}

// Send buffered spans and free resources.
$tracerProvider->shutdown();

function handleRequest($tracer, $redis) {
    $span = $tracer->spanBuilder('handle-request')->startSpan();
    $spanScope = $span->activate();

    $value = $redis->get('count');
    $redis->set('counter', (int)$value + 1);

    $redis->multi()
          ->set('key1', 'val1')
          ->get('key1')
          ->set('key2', 'val2')
          ->get('key2')
          ->exec();

    $spanScope->detach();
    $span->end();

    return $span;
}
