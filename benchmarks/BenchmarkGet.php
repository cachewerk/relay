<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkGet extends Support\Benchmark {
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function getName(): string {
        return 'GET';
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        $this->keys = $this->loadJson('meteorites.json');
    }

    protected function runBenchmark($client): int {
        foreach ($this->keys as $key) {
            $client->get($key);
        }

        return count($this->keys);
    }

    public function benchmarkPredis() {
        return $this->runBenchmark($this->predis);
    }

    public function benchmarkPhpRedis() {
        return $this->runBenchmark($this->phpredis);
    }

    public function benchmarkRelayNoCache() {
        return $this->runBenchmark($this->relayNoCache);
    }

    public function benchmarkRelay() {
        return $this->runBenchmark($this->relay);
    }
}
