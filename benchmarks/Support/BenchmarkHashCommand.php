<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class BenchmarkHashCommand extends Benchmark
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    /**
     * @var array<int, string>
     */
    protected array $mems = [];

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
        $mems = [];
        $redis = $this->createPredis();
        $items = $this->loadJsonFile('meteorites.json');

        foreach ($items as $item) {
            $redis->hmset((string) $item['id'], $this->flattenArray($item));
            $this->keys[] = $item['id'];

            foreach (array_keys($item) as $key) {
                if (! isset($mems[$key])) {
                    $mems[$key] = 0;
                }

                $mems[$key]++;
            }
        }

        arsort($mems);

        $this->mems = array_map('strval', array_keys($mems));
    }
}
