<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkRpush extends Support\Benchmark
{
    /**
     * @var array<int|string, array<int, mixed>>
     */
    protected array $data;

    public function getName(): string
    {
        return 'RPUSH';
    }

    public static function flags(): int
    {
        return self::LIST | self::WRITE;
    }

    public function seedKeys(): void
    {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $this->data[$item['id']] = array_values($this->flattenArray($item));
        }
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seedKeys();
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int
    {
        foreach ($this->data as $key => $elements) {
            $client->rpush($key, ...$elements);
        }

        return count($this->data);
    }

    public function benchmarkPredis(): int
    {
        foreach ($this->data as $key => $elements) {
            $this->predis->rpush((string) $key, $elements);
        }

        return count($this->data);
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
