<?php

namespace CacheWerk\Relay\Benchmarks\Throughput;

use CacheWerk\Relay\Benchmarks\BenchCase;

/**
 * @BeforeClassMethods("setUp")
 */
class GetThroughputBench extends BenchCase
{
    /**
     * Seed Redis with random data.
     */
    public static function setUp(): void
    {
        $redis = static::redis();
        $redis->flushdb(true);

        foreach (static::loadJson('dataset-meteorites.json') as $landing) {
            $redis->set("meteorite:{$landing->id}", $landing);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     *
     * @param  array<array<mixed>>  $params
     */
    public function benchGetThroughputOfPredis($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->predis->get($key);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     *
     * @param  array<array<string>>  $params
     */
    public function benchGetThroughputOfCredis($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->credis->get($key);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     *
     * @param  array<array<string>>  $params
     */
    public function benchGetThroughputOfPhpRedis($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->phpredis->get($key);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     *
     * @param  array<array<mixed>>  $params
     */
    public function benchGetThroughputOfRelay($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->relay->get($key);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Warmup(1)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     *
     * @param  array<array<mixed>>  $params
     */
    public function benchGetThroughputOfRelayWarmed($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->relay->get($key);
        }
    }

    /**
     * Provides the keys for each benchmark.
     *
     * @return \Generator<string, array<mixed>>
     */
    public function provideKeys()
    {
        $keys = [];

        foreach (static::loadJson('dataset-meteorites.json') as $landing) {
            $keys[] = "meteorite:{$landing->id}";
        }

        yield 'dataset' => [
            'keys' => $keys,
        ];
    }
}
