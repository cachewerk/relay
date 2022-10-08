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
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Throughput_Predis($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->predis->get($key);
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Throughput_Credis($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->credis->get($key);
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Throughput_PhpRedis($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->phpredis->get($key);
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Throughput_Relay_NoCache($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->relay->get($key);
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelayCache")
     * @Groups("relay")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Throughput_Relay_ColdCache($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->relayCache->get($key);
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Warmup(1)
     * @Sleep(100000)
     * @OutputTimeUnit("seconds", precision=2)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelayCache")
     * @Groups("relay")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Throughput_Relay_WarmCache($params): void
    {
        foreach ($params['keys'] as $key) {
            $this->relayCache->get($key);
        }
    }

    /**
     * Provides the keys for each benchmark.
     *
     * @return \Generator<string, array{keys: array<int, string>}>
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
