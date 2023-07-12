<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkHmset extends Benchmark
{
    /**
     * @var array<int|string, array<int|string, string>>
     */
    protected array $data;

    public function getName(): string
    {
        return 'HMSET';
    }

    protected function cmd(): string
    {
        return 'HMSET';
    }

    public static function flags(): int
    {
        return self::HASH | self::WRITE;
    }

    public function seedKeys(): void
    {

    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $this->data[$item['id']] = $this->flattenArray($item);
        }
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->data as $key => $value) {
            $client->hmset($key, $value);
        }

        return count($this->data);
    }
}
