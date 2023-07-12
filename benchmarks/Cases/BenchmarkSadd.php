<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkSADD extends Benchmark
{
    /**
     * @var array<int|string, array<int, mixed>>
     */
    protected array $data;

    public static function flags(): int
    {
        return self::SET | self::WRITE;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $this->data[$item['id']] = array_values($this->flattenArray($item));
        }
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->data as $key => $value) {
            $client->sadd($key, $value);
        }

        return count($this->data);
    }
}
