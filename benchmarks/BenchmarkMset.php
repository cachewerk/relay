<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkMset extends Support\Benchmark
{
    const KeysPerCall = 8;

    /**
     * @var array<int|string, array<int|string, string>>
     */
    protected array $keyChunks;

    public function getName(): string
    {
        return 'MSET';
    }

    protected function cmd(): string
    {
        return 'MSET';
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

        $keys = [];

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $keys[$item['id']] = serialize($item);
        }

        $this->keyChunks = array_chunk($keys, self::KeysPerCall, true);
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int
    {
        foreach ($this->keyChunks as $chunk) {
            $client->mset($chunk);
        }

        return count($this->keyChunks);
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
