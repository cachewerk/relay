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
        $this->keys = $this->seedSimpleKeys();
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $key) {
            $client->lrange($key, 0, -1);
        }

        return count($this->keys);
    }
}
