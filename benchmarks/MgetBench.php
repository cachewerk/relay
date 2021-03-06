<?php

namespace Relay\Benchmarks;

use Redis;
use Relay\Relay;

/**
 * @BeforeClassMethods("setUp")
 */
class MgetBench extends BenchCase
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
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     */
    public function benchMgetUsingPredis($params)
    {
        foreach ($params['chunks'] as $keys) {
            array_map('unserialize', $this->predis->mget($keys));
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     */
    public function benchMgetUsingCredis($params)
    {
        foreach ($params['chunks'] as $keys) {
            array_map('unserialize', $this->credis->mget($keys));
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     */
    public function benchMgetUsingPhpRedis($params)
    {
        $this->phpredis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

        foreach ($params['chunks'] as $keys) {
            $this->phpredis->mget($keys);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     */
    public function benchMgetUsingRelay($params)
    {
        $this->relay->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

        foreach ($params['chunks'] as $keys) {
            $this->relay->mget($keys);
        }
    }

    /**
     * @Revs(1)
     * @Iterations(10)
     * @Warmup(1)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     */
    public function benchMgetUsingRelayWarmed($params)
    {
        $this->relay->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

        foreach ($params['chunks'] as $keys) {
            $this->relay->mget($keys);
        }
    }

    /**
     * Provides the keys for each benchmark.
     *
     * @return Generator
     */
    public function provideChunks()
    {
        $keys = [];

        foreach (static::loadJson('dataset-medium.json') as $set) {
            foreach ($set as $key => $value) {
                $keys[] = "{$set->_id}:{$key}";
            }
        }

        yield 'dataset' => [
            'chunks' => array_chunk($keys, 12),
        ];
    }
}
