<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class BenchmarkSetCommand extends Benchmark
{
    /**
     * @var array<int, string>
     */
    public array $keys;

    /**
     * @var array<int, int|string>
     */
    public array $mems = [];

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function warmup(int $times, string $method): void
    {
        if ($times == 0) {
            return;
        }

        parent::warmup($times, $method);

        $this->readSimpleKeys($this->keys);
    }

    public function seed(): void
    {
        $redis = $this->createPredis();

        $mems = [];

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $redis->sadd((string) $item['id'], array_keys($this->flattenArray($item)));
            $this->keys[] = $item['id'];

            foreach (array_keys($item) as $mem) {
                $mems[$mem] = true;
            }
        }

        $this->mems = array_keys($mems);
    }
}
