<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmarks\SetCommand;

class BenchmarkSISMEMBER extends SetCommand
{
    public static function flags(): int
    {
        return self::SET | self::READ;
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $key) {
            foreach ($this->mems as $mem) {
                $client->sismember($key, $mem);
            }
        }

        return count($this->keys) * count($this->mems);
    }
}
