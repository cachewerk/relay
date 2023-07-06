<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class BenchmarkHashCommand extends Benchmark {
    /**
     * @var array<int, string>
     */
    protected array $keys;

    /**
     * @var array<int, string>
     */
    protected array $mems = [];

    public function warmup(int $times, string $method): void {
        if ($times == 0)
            return;

        parent::warmup($times, $method);

        foreach ($this->keys as $key) {
            $this->relay->hgetall( (string) $key);
        }
    }

    public function seedKeys(): void {
        $redis = $this->createPredis();

        $mems = [];

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $redis->hmset((string)$item['id'], $this->flattenArray($item));
            $this->keys[] = $item['id'];

            foreach (array_keys($item) as $key) {
                if ( ! isset($mems[$key])) {
                    $mems[$key] = 0;
                }

                $mems[$key]++;
            }
        }

        arsort($mems);

        $this->mems = array_map(function ($v) { return (string) $v; }, array_keys($mems));
    }

    public function setUp(): void {
        $this->flush();
        $this->setUpClients();
        $this->seedKeys();
    }
}
