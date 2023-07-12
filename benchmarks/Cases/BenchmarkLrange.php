<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkLRANGE extends Benchmark
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public static function flags(): int
    {
        return self::LIST | self::READ;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function seed(): void
    {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $redis->rpush((string) $item['id'], $this->flattenArray($item));
            $this->keys[] = $item['id'];
        }
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $key) {
            $client->lrange($key, 0, -1);
        }

        return count($this->keys);
    }
}
