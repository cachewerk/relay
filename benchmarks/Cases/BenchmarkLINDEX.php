<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkLINDEX extends Benchmark
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

    public function warmup(int $times, string $method): void
    {
        if ($times == 0) {
            return;
        }

        parent::warmup($times, $method);
        $this->readSimpleKeys($this->keys);
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $i => $key) {
            $client->lindex($key, $i % 32);
        }

        return count($this->keys);
    }
}
