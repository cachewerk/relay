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
     * @param  array<array<string>>  $params
     */
    public function GET_Unserialize_Predis($params): void
    {
        foreach ($params['keys'] as $key) {
            unserialize((string) $this->predis->get($key));
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
    public function GET_Unserialize_Credis($params): void
    {
        foreach ($params['keys'] as $key) {
            unserialize($this->credis->get($key)); // @phpstan-ignore-line
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
    public function GET_Unserialize_PhpRedis($params): void
    {
        $this->phpredis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

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
    public function GET_Unserialize_Relay_NoCache($params): void
    {
        $this->relay->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

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
    public function GET_Unserialize_Relay_ColdCache($params): void
    {
        $this->relayCache->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

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
    public function GET_Unserialize_Relay_WarmCache($params): void
    {
        $this->relayCache->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_PHP);

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
