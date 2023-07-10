<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkZadd extends Support\Benchmark
{
    /**
     * @var array<int|string, array<float|string>>
     */
    protected array $data;

    public function getName(): string
    {
        return 'ZADD';
    }

    protected function cmd(): string
    {
        return 'ZADD';
    }

    public static function flags(): int
    {
        return self::ZSET | self::WRITE;
    }

    public function seedKeys(): void
    {

    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        $rng = mt_rand() / mt_getrandmax();

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $args = [];

            foreach ($item as $key => $val) {
                $args[] = round($rng * strlen(serialize($val)), 4);
                $args[] = $key;
            }

            $this->data[$item['id']] = $args;
        }
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int
    {
        foreach ($this->data as $key => $value) {
            $client->zadd($key, ...$value);
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
