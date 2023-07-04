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

    public function seedKeys(): void {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $redis->set((string)$item['id'], serialize($item));
            $this->keys[] = $item['id'];
        }
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seedKeys();
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int {
        foreach ($this->keys as $key) {
            $client->get($key);
        }

        return count($this->keys);
    }

    public function benchmarkPredis(): int {
        return $this->runBenchmark($this->predis);
    }

    public function benchmarkPhpRedis(): int {
        return $this->runBenchmark($this->phpredis);
    }

    public function benchmarkRelayNoCache(): int {
        return $this->runBenchmark($this->relayNoCache);
    }

    public function benchmarkRelay(): int {
        return $this->runBenchmark($this->relay);
    }
}
