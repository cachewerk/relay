<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkMGET extends Benchmark
{
    const KeysPerCall = 8;

    /**
     * @var array<int, array<int, string>>
     */
    protected array $keyChunks;

    public static function flags(): int
    {
        return self::STRING | self::READ | self::DEFAULT;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function seed(): void
    {
        $keys = [];

        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $redis->set((string) $item['id'], serialize($item));
            $keys[] = $item['id'];
        }

        $this->keyChunks = array_chunk($keys, self::KeysPerCall);
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->keyChunks as $chunk) {
            $client->mget($chunk);
        }

        return count($this->keyChunks);
    }
}
