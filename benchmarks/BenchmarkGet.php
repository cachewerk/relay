<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkGet extends Support\Benchmark
{
    const Name = 'GET';

    const Operations = 1000;

    const Iterations = 5;

    const Revolutions = 10;

    const Warmup = 1;

    protected array $keys;

    public function setUp(): void
    {
        $this->redis()->flushall();

        $this->keys = $this->loadJson('meteorites.json');

        $this->predis = $this->setUpPredis();
        $this->phpredis = $this->setUpPhpRedis();
        $this->relay = $this->setUpRelay();
        $this->relayCache = $this->setUpRelayCache();
    }

    public function benchmarkPredis(): void
    {
        foreach ($this->keys as $key) {
            $this->predis->get((string) $key);
        }
    }

    public function benchmarkPhpRedis(): void
    {
        foreach ($this->keys as $key) {
            $this->phpredis->get((string) $key);
        }
    }

    public function benchmarkRelay(): void
    {
        foreach ($this->keys as $key) {
            $this->relay->get((string) $key);
        }
    }

    public function benchmarkRelayCache(): void
    {
        foreach ($this->keys as $key) {
            $this->relayCache->get((string) $key);
        }
    }
}
