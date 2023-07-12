<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkSet extends Benchmark
{
    /**
     * @var array<int|string, string>
     */
    protected array $data;

    public function getName(): string
    {
        return 'SET';
    }

    protected function cmd(): string
    {
        return 'SET';
    }

    public static function flags(): int
    {
        return self::STRING | self::WRITE;
    }

    public function seedKeys(): void
    {
        //
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
