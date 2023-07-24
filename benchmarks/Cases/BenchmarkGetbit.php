<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkGETBIT extends Benchmark
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

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

    public static function flags(): int
    {
        return self::STRING | self::READ;
    }

    public function warmup(int $times, string $method): void
    {
        if ($times == 0) {
            return;
        }

        parent::warmup($times, $method);

        $this->readSimpleKeys($this->keys);
    }

    public function runBenchmark($client): int
    {
        foreach ($this->keys as $i => $key) {
            $client->getbit($key, $i);
        }

        return count($this->keys);
    }
}
