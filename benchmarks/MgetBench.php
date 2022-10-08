<?php

namespace CacheWerk\Relay\Benchmarks;

use Redis;
use Relay\Relay;

/**
 * @BeforeClassMethods("setUp")
 */
class MgetBench extends BenchCase
{
    /**
     * Seed Redis with random data.
     */
    public static function setUp(): void
    {
        $redis = static::redis();
        $redis->flushdb(true);

        foreach (static::loadJson('dataset-medium.json') as $set) {
            foreach ($set as $key => $value) {
                $redis->set("{$set['_id']}:{$key}", $value);
            }
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     *
     * @param  array{chunks: array<int, array<int, string>>}  $params
     */
    public function MGET_Predis($params): void
    {
        foreach ($params['chunks'] as $keys) {
            array_map('unserialize', $this->predis->mget($keys));
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     *
     * @param  array{chunks: array<int, array<int, string>>}  $params
     */
    public function MGET_Credis($params): void
    {
        foreach ($params['chunks'] as $keys) {
            array_map('unserialize', (array) $this->credis->mGet($keys));
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     *
     * @param  array{chunks: array<int, array<int, string>>}  $params
     */
    public function MGET_PhpRedis($params): void
    {
        $this->phpredis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

        foreach ($params['chunks'] as $keys) {
            $this->phpredis->mget($keys);
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     *
     * @param  array{chunks: array<int, array<int, string>>}  $params
     */
    public function MGET_Relay_NoCache($params): void
    {
        $this->relay->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

        foreach ($params['chunks'] as $keys) {
            $this->relay->mget($keys);
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpRelayCache")
     * @Groups("relay")
     *
     * @param  array{chunks: array<int, array<int, string>>}  $params
     */
    public function MGET_Relay_ColdCache($params): void
    {
        $this->relayCache->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

        foreach ($params['chunks'] as $keys) {
            $this->relayCache->mget($keys);
        }
    }

    /**
     * @Subject
     * @Revs(1)
     * @Iterations(10)
     * @Warmup(1)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideChunks")
     * @BeforeMethods("setUpRelayCache")
     * @Groups("relay")
     *
     * @param  array{chunks: array<int, array<int, string>>}  $params
     */
    public function MGET_Relay_WarmCache($params): void
    {
        $this->relayCache->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

        foreach ($params['chunks'] as $keys) {
            $this->relayCache->mget($keys);
        }
    }

    /**
     * Provides the keys for each benchmark.
     *
     * @return \Generator<string, array{chunks: array<int, array<int, string>>}>
     */
    public function provideChunks()
    {
        $keys = [];

        foreach (static::loadJson('dataset-medium.json') as $set) {
            foreach ($set as $key => $value) {
                $keys[] = "{$set['_id']}:{$key}";
            }
        }

        yield 'dataset' => [
            'chunks' => array_chunk($keys, 12),
        ];
    }
}
