<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkSet extends Support\Benchmark
{
    /**
     * @var array<int|string, string>
     */
    protected array $data;

    public function getName(): string
    {
        return 'SET';
    }

    protected function cmd(): string
    {
        return 'SET';
    }

    public static function flags(): int
    {
        return self::STRING | self::WRITE;
    }

    public function seedKeys(): void
    {

    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $this->data[$item['id']] = serialize($item);
        }
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int
    {
        foreach ($this->data as $key => $value) {
            $client->set($key, $value);
        }

        return count($this->data);
    }

    public function benchmarkPredis(): int
    {
        return $this->runBenchmark($this->predis);
    }

    public function benchmarkPhpRedis(): int
    {
        return $this->runBenchmark($this->phpredis);
    }

    public function benchmarkRelayNoCache(): int
    {
        return $this->runBenchmark($this->relayNoCache);
    }

    public function benchmarkRelay(): int
    {
        return $this->runBenchmark($this->relay);
    }
}
