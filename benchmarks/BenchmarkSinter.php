<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkSinter extends Support\Benchmark {
    const KeysPerCall = 8;

    /**
     * @var array<int, Array<int, string>>
     */
    protected array $keyChunks;

    public function getName(): string {
        return 'SINTER';
    }

    public function warmup(int $times, string $method): void {
        if ($times == 0)
            return;

        parent::warmup($times, $method);

        foreach ($this->keyChunks as $chunk) {
            foreach ($chunk as $key) {
                $this->relay->smembers((string)$key);
            }
        }
    }

    public function seedKeys(): void {
        $keys = [];

        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $redis->sadd((string) $item['id'], array_keys($this->flattenArray($item)));
            $keys[] = $item['id'];
        }

        $this->keyChunks = array_chunk($keys, self::KeysPerCall);
    }

    public function setUp(): void {
        $this->flush();
        $this->setUpClients();
        $this->seedKeys();
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int {
        foreach ($this->keyChunks as $chunk) {
            $client->sinter($chunk);
        }
        return count($this->keyChunks);
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
