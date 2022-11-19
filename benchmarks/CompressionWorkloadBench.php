<?php

namespace CacheWerk\Relay\Benchmarks\Get;

use Redis;
use Relay\Relay;

use CacheWerk\Relay\Benchmarks\BenchCase;

/**
 * @BeforeClassMethods("setUp")
 */
class CompressionWorkloadBench extends BenchCase
{
    /**
     * Seed Redis with random data.
     */
    public static function setUp(): void
    {
        $redis = static::redis();
        $redis->flushdb(true);
    }

    /**
     * @Subject
     * @Revs(2)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideData")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     *
     * @param  array{data: array<string, mixed>}  $params
     */
    public function SET_GET_zstd_igbinary_Predis($params): void
    {
        // Ideally we'd skip this benchmark (trigger a notice), but PHPBench won't allow either
        $compress = function_exists('zstd_compress') ? 'zstd_compress' : 'gzcompress';
        $uncompress = function_exists('zstd_compress') ? 'zstd_uncompress' : 'gzuncompress';

        foreach ($params['data'] as $key => $value) {
            $this->predis->set($key, $compress(igbinary_serialize($value)));

            for ($i = 0; $i < 10; $i++) {
                igbinary_unserialize($uncompress($this->predis->get($key)));
            }
        }
    }

    /**
     * @Subject
     * @Revs(2)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideData")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     *
     * @param  array{data: array<string, mixed>}  $params
     */
    public function SET_GET_zstd_igbinary_Credis($params): void
    {
        // Ideally we'd skip this benchmark (trigger a notice), but PHPBench won't allow either
        $compress = function_exists('zstd_compress') ? 'zstd_compress' : 'gzcompress';
        $uncompress = function_exists('zstd_compress') ? 'zstd_uncompress' : 'gzuncompress';

        foreach ($params['data'] as $key => $value) {
            $this->credis->set($key, $compress(igbinary_serialize($value)));

            for ($i = 0; $i < 10; $i++) {
                igbinary_unserialize($uncompress($this->credis->get($key)));
            }
        }
    }

    /**
     * @Subject
     * @Revs(2)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideData")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     *
     * @param  array{data: array<string, mixed>}  $params
     */
    public function SET_GET_zstd_igbinary_PhpRedis($params): void
    {
        $this->phpredis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
        $this->phpredis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_ZSTD);

        foreach ($params['data'] as $key => $value) {
            $this->phpredis->set($key, $value);

            for ($i = 0; $i < 10; $i++) {
                $this->phpredis->get($key);
            }
        }
    }

    /**
     * @Subject
     * @Revs(2)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideData")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     *
     * @param  array{data: array<string, mixed>}  $params
     */
    public function SET_GET_zstd_igbinary_Relay_NoCache($params): void
    {
        $this->relay->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_IGBINARY);
        $this->relay->setOption(Relay::OPT_COMPRESSION, Relay::COMPRESSION_ZSTD);

        foreach ($params['data'] as $key => $value) {
            $this->relay->set($key, $value);

            for ($i = 0; $i < 10; $i++) {
                $this->relay->get($key);
            }
        }
    }

    /**
     * @Subject
     * @Revs(2)
     * @Iterations(2)
     * @Warmup(1)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideData")
     * @BeforeMethods("setUpRelayCache")
     * @Groups("relay")
     *
     * @param  array{data: array<string, mixed>}  $params
     */
    public function SET_GET_zstd_igbinary_Relay_WarmCache($params): void
    {
        $this->relayCache->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_IGBINARY);
        $this->relayCache->setOption(Relay::OPT_COMPRESSION, Relay::COMPRESSION_ZSTD);

        foreach ($params['data'] as $key => $value) {
            $this->relayCache->set($key, $value);

            for ($i = 0; $i < 10; $i++) {
                $this->relayCache->get($key);
            }
        }
    }

    /**
     * Provides the data for each benchmark.
     *
     * @return \Generator<string, array{data: array<non-falsy-string, mixed>}>
     */
    public function provideData()
    {
        $data = [];

        foreach (static::loadJson('dataset-medium.json') as $set) {
            foreach ($set as $key => $value) {
                $data["{$set['_id']}:{$key}"] = $value;
            }
        }

        yield 'dataset' => [
            'data' => $data,
        ];
    }
}
