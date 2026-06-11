<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkLPOS extends Benchmark
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    /**
     * @var array<int, string>
     */
    protected array $mems;

    public static function flags(): int
    {
        return self::LIST | self::READ;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function seed(): void
    {
        $mems = [];

        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $key_mems = $this->flattenArray($item);
            foreach ($key_mems as $mem) {
                $mems[$mem] = $mem;
            }

            $redis->rpush((string) $item['id'], $key_mems);
            $this->keys[] = (string) $item['id'];
        }

        $this->mems = array_keys($mems);
    }

    public function warmup(int $times, string $method): void
    {
        if ($times == 0) {
            return;
        }

        parent::warmup($times, $method);
        $this->readSimpleKeys($this->keys);
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $i => $key) {
            $client->lpos( // @phpstan-ignore-line
                $key,
                $this->mems[$i % count($this->mems)]
            );
        }

        return count($this->keys);
    }

    /**
     * Predis does not specifically implement 'LPOS'
     */
    public function benchmarkPredis(): int
    {
        foreach ($this->keys as $i => $key) {
            $this->predis->executeRaw([
                'LPOS',
                $key,
                $this->mems[$i % count($this->mems)],
            ]);
        }

        return count($this->keys);
    }
}
