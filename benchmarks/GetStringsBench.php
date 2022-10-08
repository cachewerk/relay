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
     * @Revs(10)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Strings_Predis(array $params): void
    {
        foreach ($params['keys'] as $key) {
            $this->predis->get((string) $key);
        }
    }

    /**
     * @Subject
     * @Revs(10)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Strings_Credis(array $params): void
    {
        foreach ($params['keys'] as $key) {
            $this->credis->get($key);
        }
    }

    /**
     * @Subject
     * @Revs(10)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Strings_PhpRedis(array $params): void
    {
        foreach ($params['keys'] as $key) {
            $this->phpredis->get($key);
        }
    }

    /**
     * @Subject
     * @Revs(10)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     *
     * @param  array{keys: array<int, string>}  $params
     */
    public function GET_Strings_Relay_NoCache(array $params): void
    {
        foreach ($params['keys'] as $key) {
            $this->relay->get($key);
        }
    }

    /**
     * @Subject
     * @Revs(10)
     * @Iterations(2)
     * @Warmup(1)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelayCache")
     * @Groups("relay")
     *
     * @param  array{keys: array<int, string>}  $params
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
     * @return \Generator<string, array{keys: array<int, string>}>
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
