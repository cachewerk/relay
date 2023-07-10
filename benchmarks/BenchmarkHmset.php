<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkHmset extends Support\Benchmark
{
    /**
     * @var array<int|string, array<int|string, string>>
     */
    protected array $data;

    public function getName(): string
    {
        return 'HMSET';
    }

    protected function cmd(): string
    {
        return 'HMSET';
    }

    public static function flags(): int
    {
        return self::HASH | self::WRITE;
    }

    public function seedKeys(): void
    {

    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $this->data[$item['id']] = $this->flattenArray($item);
        }
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int
    {
        foreach ($this->data as $key => $value) {
            $client->hmset($key, $value);
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
