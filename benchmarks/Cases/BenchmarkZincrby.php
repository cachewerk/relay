<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkZINCRBY extends Benchmark
{
    /**
     * @var array<int|string, array<int|string, float>>
     */
    protected array $keys;

    public static function flags(): int
    {
        return self::ZSET | self::READ;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function seed(): void
    {
        $rng = mt_rand() / mt_getrandmax();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $rec = [];

            $rng = round(mt_rand() / mt_getrandmax(), 4);

            foreach ($this->flattenArray($item) as $key => $val) {
                $rec[$key] = $rng * strlen(serialize($val));
            }

            $this->keys[$item['id']] = $rec;
        }
    }

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
}
