<?php

namespace CacheWerk\Relay\Benchmarks\Get;

use Redis;
use Relay\Relay;

use CacheWerk\Relay\Benchmarks\BenchCase;

class DataCompression extends BenchCase
{
    /**
     * @Subject
     * @Revs(10)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideData")
     * @BeforeMethods("setUpPredis")
     * @Groups("predis")
     *
     * @param  array{data: array<string, mixed>}  $params
     */
    public function zstd_igbinary_Predis($params): void
    {
        // Ideally we'd skip this benchmark (trigger a notice), but PHPBench won't allow either
        $compress = function_exists('zstd_compress') ? 'zstd_compress' : 'gzcompress';
        $uncompress = function_exists('zstd_compress') ? 'zstd_uncompress' : 'gzuncompress';

        foreach ($params['data'] as $value) {
            $value = $compress(igbinary_serialize($value));

            for ($i = 0; $i < 10; $i++) {
                igbinary_unserialize($uncompress($value));
            }
        }
    }

    /**
     * @Subject
     * @Revs(10)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideData")
     * @BeforeMethods("setUpCredis")
     * @Groups("credis")
     *
     * @param  array{data: array<string, mixed>}  $params
     */
    public function zstd_igbinary_Credis($params): void
    {
        // Ideally we'd skip this benchmark (trigger a notice), but PHPBench won't allow either
        $compress = function_exists('zstd_compress') ? 'zstd_compress' : 'gzcompress';
        $uncompress = function_exists('zstd_compress') ? 'zstd_uncompress' : 'gzuncompress';

        foreach ($params['data'] as $value) {
            $value = $compress(igbinary_serialize($value));

            for ($i = 0; $i < 10; $i++) {
                igbinary_unserialize($uncompress($value));
            }
        }
    }

    /**
     * @Subject
     * @Revs(10)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideData")
     * @BeforeMethods("setUpPhpRedis")
     * @Groups("phpredis")
     *
     * @param  array{data: array<string, mixed>}  $params
     */
    public function zstd_igbinary_PhpRedis($params): void
    {
        $this->phpredis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
        $this->phpredis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_ZSTD);

        foreach ($params['data'] as $value) {
            $value = $this->phpredis->_pack($value);

            for ($i = 0; $i < 10; $i++) {
                $this->phpredis->_unpack($value);
            }
        }
    }

    /**
     * @Subject
     * @Revs(10)
     * @Iterations(2)
     * @Sleep(100000)
     * @OutputTimeUnit("milliseconds", precision=3)
     * @ParamProviders("provideData")
     * @BeforeMethods("setUpRelay")
     * @Groups("relay")
     *
     * @param  array{data: array<string, mixed>}  $params
     */
    public function zstd_igbinary_Relay($params): void
    {
        $this->relay->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_IGBINARY);
        $this->relay->setOption(Relay::OPT_COMPRESSION, Relay::COMPRESSION_ZSTD);

        foreach ($params['data'] as $value) {
            $value = $this->relay->_pack($value);

            for ($i = 0; $i < 10; $i++) {
                $this->relay->_unpack($value);
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
