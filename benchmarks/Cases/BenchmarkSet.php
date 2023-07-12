<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkSET extends Benchmark
{
    /**
     * @var array<int|string, string>
     */
    protected array $data;

    public static function flags(): int
    {
        return self::STRING | self::WRITE;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $this->data[$item['id']] = serialize($item);
        }
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->data as $key => $value) {
            $client->set($key, $value);
        }

        return count($this->data);
    }
}
