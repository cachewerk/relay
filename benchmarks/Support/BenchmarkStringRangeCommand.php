<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class BenchmarkStringRangeCommand extends Benchmark
{
    /**
     * @var array<int|string, int[]>
     */
    protected array $args;

    public static function flags(): int
    {
        return self::STRING | self::READ;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function seed(): void
    {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $key = $item['id'];
            $val = serialize($item);

            $redis->set($key, $val);

            $this->args[$key] = $this->pickRandomRange(strlen($val));
        }
    }

    public function warmup(int $times, string $method): void
    {
        if ($times == 0) {
            return;
        }

        parent::warmup($times, $method);

        foreach (array_keys($this->args) as $key) {
            $this->relay->get((string) $key);
        }
    }

    /**
     * @return int[]
     */
    protected function pickRandomRange(int $len): array
    {
        $start = rand(-$len, $len - 1);
        $end = rand(-$len, $len - 1);

        if ($start > $end) {
            return [$end, $start];
        } else {
            return [$start, $end];
        }
    }

    protected function runBenchmark($client): int
    {
        $cmd = $this->command();

        foreach ($this->args as $key => [$start, $end]) {
            $client->{$cmd}($key, $start, $end);
        }

        return count($this->args);
    }
}
