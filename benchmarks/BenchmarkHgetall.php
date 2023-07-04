<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkHgetall extends Support\Benchmark {
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function getName(): string {
        return 'HGETALL';
    }

    /**
     * @param array<mixed> $input
     * @return array<mixed>
     *
     * Helper function to flatten a multidimensional array.  No type hinting here
     * as it can operate on any arbitrary array data.
     */
    protected function flattenArray(array $input, string $prefix = ''): array {
        $result = [];

        foreach ($input as $key => $val) {
            if (is_array($val)) {
                $result = $result + $this->flattenArray($val, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $val;
            }
        }

        return $result;
    }

    public function seedKeys(): void {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $redis->hmset((string)$item['id'], $this->flattenArray($item));
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
            $client->hgetall($key);
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
