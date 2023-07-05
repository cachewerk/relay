<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkSismember extends Support\Benchmark {
    /**
     * @var array<int, string>
     */
    protected array $keys;

    /**
     * @var array<int, int|string>
     */
    protected array $mems = [];

    public function getName(): string {
        return 'SISMEMBER';
    }

    public function warmup(int $times, string $method): void {
        if ($times == 0)
            return;

        parent::warmup($times, $method);

        foreach ($this->keys as $key) {
            $this->relay->smembers((string)$key);
        }
    }

    public function seedKeys(): void {
        $redis = $this->createPredis();

        $mems = [uniqid() => true];

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $redis->sadd((string)$item['id'], array_keys($this->flattenArray($item)));
            $this->keys[] = $item['id'];

            foreach (array_keys($item) as $mem) {
                $mems[$mem] = true;
            }
        }

        $this->mems = array_keys($mems);
    }

    public function setUp(): void {
        $this->flush();
        $this->setUpClients();
        $this->seedKeys();
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int {
        foreach ($this->keys as $key) {
            foreach ($this->mems as $mem) {
                $client->sismember($key, $mem);
            }
        }

        return count($this->keys) * count($this->mems);
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
