<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class BenchmarkMultiSetCommand extends Benchmark
{
    const KeysPerCall = 8;

    /**
     * @var array<int, array<int, string>>
     */
    protected array $keyChunks;

    public static function flags(): int
    {
        return self::SET | self::READ;
    }

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

        foreach ($this->keyChunks as $chunk) {
            $this->readSimpleKeys($chunk);
        }
    }

    public function seed(): void
    {
        $this->keyChunks = array_chunk($this->seedSimpleKeys(), self::KeysPerCall);
    }

    protected function runBenchmark($client): int
    {
        $cmd = $this->command();

        foreach ($this->keyChunks as $chunk) {
            $client->$cmd($chunk);
        }

        return count($this->keyChunks);
    }
}
