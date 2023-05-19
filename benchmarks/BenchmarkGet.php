<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkGET extends Support\Benchmark
{
    const Name = 'GET';

    const Operations = 1000;

    const Iterations = 5;

    const Revolutions = 50;

    const Warmup = 1;

    protected array $keys;

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        $this->keys = $this->loadJson('meteorites.json');
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

    public function benchmarkRelayNoCache(): void
    {
        foreach ($this->keys as $key) {
            $this->relayNoCache->get((string) $key);
        }
    }

    public function benchmarkRelay(): void
    {
        foreach ($this->keys as $key) {
            $this->relay->get((string) $key);
        }
    }
}
