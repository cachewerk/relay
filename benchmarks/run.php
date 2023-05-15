<?php

// TODO: ditch Compsoser...

// TODO: Mention this is SINGLE core SINGLE worker...
// TODO: Redis CPU...
// TODO: OCP compression + batching...
// TODO: benchmark how many network bytes were transferred!!!

// TODO: the dataset must be larger...
// TODO: each iteration should be 1 sec?

require __DIR__ . '/../vendor/autoload.php';

$iterations = 10;

$benchmarks = [
    CacheWerk\Relay\Benchmarks\BenchmarkGet::class,
];

$runner = new CacheWerk\Relay\Benchmarks\Support\Runner(
    $_SERVER['REDIS_HOST'] ?? '127.0.0.1',
    $_SERVER['REDIS_PORT'] ?? 6379,
);

$runner->run($benchmarks, $iterations);
