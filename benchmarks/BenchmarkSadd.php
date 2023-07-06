<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkSadd extends Support\Benchmark {
    /**
     * @var array<int|string, array<int, mixed>>
     */
    protected array $data;

    public function getName(): string {
        return 'SADD';
    }

    protected function cmd(): string {
        return 'SADD';
    }

    public static function flags(): int {
        return self::SET | self::WRITE;
    }

    public function seedKeys(): void {

    }

    public function setUp(): void {
        $this->flush();
        $this->setUpClients();

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $this->data[$item['id']] = array_values($this->flattenArray($item));
        }
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int {
        foreach ($this->data as $key => $value) {
            $client->sadd($key, $value);
        }
        return count($this->data);
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
