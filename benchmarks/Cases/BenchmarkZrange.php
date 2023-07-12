<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkZrange extends Benchmark
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function getName(): string
    {
        return 'ZRANGE';
    }

    public static function flags(): int
    {
        return self::ZSET | self::READ;
    }

    public function seedKeys(): void
    {
        $redis = $this->createPredis();

        $rng = mt_rand() / mt_getrandmax();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $args = [];

            foreach ($item as $key => $val) {
                $args[] = round($rng * strlen(serialize($val)), 4);
                $args[] = $key;
            }

            $redis->zadd($item['id'], ...$args);
            $this->keys[] = $item['id'];
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
        foreach ($this->keys as $key) {
            $client->zrange($key, 0, -1);
        }

        return count($this->keys);
    }
}
