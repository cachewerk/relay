<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkSetCommand;

class BenchmarkSmismember extends BenchmarkSetCommand
{
    public function getName(): string
    {
        return 'SMISMEMBER';
    }

    public static function flags(): int
    {
        return self::SET | self::READ;
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $key) {
            $client->smismember($key, $this->mems);
        }

        return count($this->keys);
    }

    public function benchmarkPredis(): int
    {
        foreach ($this->keys as $key) {
            /** @phpstan-ignore-next-line */
            $this->predis->smismember($key, ...$this->mems);
        }

        return count($this->keys);
    }
}
