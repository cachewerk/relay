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
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     *
     * @param  array<array<mixed>>  $params
     */
    public function GET_Strings_Predis(array $params): void
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
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     *
     * @param  array<array<string>>  $params
     */
    public function GET_Strings_Credis(array $params): void
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
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     *
     * @param  array<array<string>>  $params
     */
    public function GET_Strings_PhpRedis(array $params): void
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
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     *
     * @param  array<array<mixed>>  $params
     */
    public function GET_Strings_Relay_NoCache(array $params): void
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
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelayCache")
     * @Groups("relay")
     *
     * @param  array<array<mixed>>  $params
     */
    public function GET_Strings_Relay_ColdCache(array $params): void
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
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelayCache")
     * @Groups("relay")
     *
     * @param  array<array<mixed>>  $params
     */
    public function GET_Strings_Relay_WarmCache(array $params): void
    {
        foreach ($params['keys'] as $key) {
            $this->relayCache->get($key);
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

        foreach (static::loadJson('dataset-medium.json') as $set) {
            foreach ($set as $key => $value) {
                $keys[] = "{$set['_id']}:{$key}";
            }
        }

        yield 'dataset' => [
            'keys' => $keys,
        ];
    }
}
