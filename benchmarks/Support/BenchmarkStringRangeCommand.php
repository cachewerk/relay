<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class BenchmarkStringRangeCommand extends Benchmark
{
    /**
     * @var array<int|string, int[]>
     */
    protected array $args;

    abstract public function cmd(): string;

    public static function flags(): int
    {
        return self::STRING | self::READ;
    }

    public function seedKeys(): void
    {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $key = $item['id'];
            $val = serialize($item);

            $redis->set($key, $val);

            $this->args[$key] = $this->pickRandomRange(strlen($val));
        }
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seedKeys();
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

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int
    {
        $cmd = $this->cmd();

        foreach ($this->args as $key => [$start, $end]) {
            $client->$cmd($key, $start, $end);
        }

        return count($this->args);
    }

    public function benchmarkPredis(): int
    {
        return $this->runBenchmark($this->predis);
    }

    public function benchmarkPhpRedis(): int
    {
        return $this->runBenchmark($this->phpredis);
    }

    public function benchmarkRelayNoCache(): int
    {
        return $this->runBenchmark($this->relayNoCache);
    }

    public function benchmarkRelay(): int
    {
        return $this->runBenchmark($this->relay);
    }
}
