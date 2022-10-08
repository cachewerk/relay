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
            $redis->set("meteorite:{$landing['id']}", $landing);
        }
    }

    /**
     * @Subject
     * @Revs(50000)
     * @Iterations(2)
     * @Sleep(1000000)
     * @OutputTimeUnit("seconds", precision=-1)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     *
     * @param  array{keys: array<string, null>}  $params
     */
    public function GET_Throughput_Predis($params): void
    {
        $this->predis->get(array_rand($params['keys']));
    }

    /**
     * @Subject
     * @Revs(50000)
     * @Iterations(2)
     * @Sleep(1000000)
     * @OutputTimeUnit("seconds", precision=-1)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     *
     * @param  array{keys: array<string, null>}  $params
     */
    public function GET_Throughput_Credis($params): void
    {
        $this->credis->get(array_rand($params['keys']));
    }

    /**
     * @Subject
     * @Revs(50000)
     * @Iterations(2)
     * @Sleep(1000000)
     * @OutputTimeUnit("seconds", precision=-1)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     *
     * @param  array{keys: array<string, null>}  $params
     */
    public function GET_Throughput_PhpRedis($params): void
    {
        $this->phpredis->get(array_rand($params['keys']));
    }

    /**
     * @Subject
     * @Revs(50000)
     * @Iterations(2)
     * @Sleep(1000000)
     * @OutputTimeUnit("seconds", precision=-1)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     *
     * @param  array{keys: array<string, null>}  $params
     */
    public function GET_Throughput_Relay_NoCache($params): void
    {
        $this->relay->get(array_rand($params['keys']));
    }

    /**
     * @Subject
     * @Revs(5000000)
     * @Iterations(2)
     * @Warmup(1)
     * @Sleep(1000000)
     * @OutputTimeUnit("seconds", precision=-1)
     * @OutputMode("throughput")
     * @ParamProviders("provideKeys")
     * @BeforeMethods("setUpRelayCache")
     * @Groups("relay")
     *
     * @param  array{keys: array<string, null>}  $params
     */
    public function GET_Throughput_Relay_WarmCache($params): void
    {
        $this->relayCache->get(array_rand($params['keys']));
    }

    /**
     * Provides the keys for each benchmark.
     *
     * @return \Generator<string, array{keys: array<string, null>}>
     */
    public function provideKeys()
    {
        $keys = [];

        foreach (static::loadJson('dataset-meteorites.json') as $landing) {
            $keys["meteorite:{$landing['id']}"] = null;
        }

        yield 'dataset' => [
            'keys' => $keys,
        ];
    }
}
