<?php

namespace CacheWerk\Relay\Benchmarks\Get;

use CacheWerk\Relay\Benchmarks\BenchCase;

/**
 * @BeforeClassMethods("setUp")
 */
class GetStringsBench extends BenchCase
{
    /**
     * Seed Redis with random data.
     *
     * @return void
     */
    public static function setUp()
    {
        $redis = static::redis();
        $redis->flushdb(true);

        foreach (static::loadJson('dataset-medium.json') as $set) {
            foreach ($set as $key => $value) {
                $redis->set("{$set->_id}:{$key}", $value);
            }
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     */
    public function benchGetStringsUsingPredis($params) {
        foreach ($params['keys'] as $key) {
            $this->predis->get($key);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     */
    public function benchGetStringsUsingCredis($params) {
        foreach ($params['keys'] as $key) {
            $this->credis->get($key);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     */
    public function benchGetStringsUsingPhpRedis($params)
    {
        foreach ($params['keys'] as $key) {
            $this->phpredis->get($key);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     */
    public function benchGetStringsUsingRelay($params)
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
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     */
    public function benchGetStringsUsingRelayWarmed($params)
    {
        foreach ($params['keys'] as $key) {
            $this->relay->get($key);
        }
    }

    /**
     * Provides the keys for each benchmark.
     *
     * @return Generator
     */
    public function provideKeys()
    {
        $keys = [];

        foreach (static::loadJson('dataset-medium.json') as $set) {
            foreach ($set as $key => $value) {
                $keys[] = "{$set->_id}:{$key}";
            }
        }

        yield 'dataset' => [
            'keys' => $keys,
        ];
    }
}
