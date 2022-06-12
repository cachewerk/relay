<?php

namespace CacheWerk\Relay\Benchmarks\Get;

use Redis;
use Relay\Relay;

use CacheWerk\Relay\Benchmarks\BenchCase;

/**
 * @BeforeClassMethods("setUp")
 */
class GetUnserializeBench extends BenchCase
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
    public function benchGetSerializedUsingPredis($params) {
        foreach ($params['keys'] as $key) {
            unserialize($this->predis->get($key));
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
    public function benchGetSerializedUsingCredis($params) {
        foreach ($params['keys'] as $key) {
            unserialize($this->credis->get($key));
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
    public function benchGetSerializedUsingPhpRedis($params)
    {
        $this->phpredis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

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
    public function benchGetSerializedUsingRelay($params)
    {
        $this->relay->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

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
    public function benchGetSerializedUsingRelayWarmed($params)
    {
        $this->relay->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

        foreach ($params['keys'] as $key) {
            $this->relay->get($key);
        }
    }

    /**
     * Provides the keys for each benchmark.
     *
     * @return \Generator<string, array>
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
