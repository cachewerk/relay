<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkMSET extends Benchmark
{
    const KeysPerCall = 8;

    /**
     * @var array<int|string, array<int|string, string>>
     */
    protected array $keyChunks;

    public static function flags(): int
    {
        return self::STRING | self::WRITE;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        $keys = [];

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $keys[$item['id']] = serialize($item);
        }

        $this->keyChunks = array_chunk($keys, self::KeysPerCall, true);
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->keyChunks as $chunk) {
            $client->mset($chunk);
        }

        return count($this->keyChunks);
    }
}
