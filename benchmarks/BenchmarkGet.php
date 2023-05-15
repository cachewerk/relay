<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkGet extends Benchmark
{
    const Name = 'GET';

    const Operations = 1000;

    const Warmup = 1;

    protected array $keys;

    public function setUp(): void
    {
        $this->redis()->flushall();

        $this->keys = $this->loadJson('meteorites.json');

        $this->predis = $this->setUpPredis();
        $this->phpredis = $this->setUpPhpRedis();
        $this->relay = $this->setUpRelay();
        $this->relayNC = $this->setUpRelayNC();
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

    public function benchmarkRelayNC(): void
    {
        foreach ($this->keys as $key) {
            $this->relayNC->get((string) $key);
        }
    }

    public function benchmarkRelay(): void
    {
        foreach ($this->keys as $key) {
            $this->relay->get((string) $key);
        }
    }
}
