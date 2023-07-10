<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkZincrby extends Support\Benchmark
{
    /**
     * @var array<int|string, array<int|string, float>>
     */
    protected array $keys;

    public function getName(): string
    {
        return 'ZINCRBY';
    }

    public static function flags(): int
    {
        return self::ZSET | self::READ;
    }

    public function seedKeys(): void
    {
        $redis = $this->createPredis();

        $rng = mt_rand() / mt_getrandmax();

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $rec = [];

            $rng = round(mt_rand() / mt_getrandmax(), 4);

            foreach ($this->flattenArray($item) as $key => $val) {
                $rec[$key] = $rng * strlen(serialize($val));
            }

            $this->keys[$item['id']] = $rec;
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
        $operations = 0;

        foreach ($this->keys as $key => $item) {
            foreach ($item as $mem => $score) {
                $client->zincrby($key, $score, $mem);
                $operations++;
            }
        }

        return $operations;
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
