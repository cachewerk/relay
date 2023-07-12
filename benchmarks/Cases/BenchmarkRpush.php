<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkRPUSH extends Benchmark
{
    /**
     * @var array<int|string, array<int, mixed>>
     */
    protected array $data;

    public static function flags(): int
    {
        return self::LIST | self::WRITE;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function seed(): void
    {
        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $this->data[$item['id']] = array_values($this->flattenArray($item));
        }
    }

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
}
