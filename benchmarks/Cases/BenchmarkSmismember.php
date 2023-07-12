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

    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $key) {
            $client->smismember($key, $this->mems);
        }

        return count($this->keys);
    }

    public function benchmarkPredis(): int
    {
        $mems = array_map('strval', $this->mems);

        foreach ($this->keys as $key) {
            $this->predis->smismember($key, ...$mems);
        }

        return count($this->keys);
    }
}
