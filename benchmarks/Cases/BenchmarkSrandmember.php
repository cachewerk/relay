<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmarks\SetCommand;

class BenchmarkSRANDMEMBER extends SetCommand
{
    public static function flags(): int
    {
        return self::SET | self::READ;
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $key) {
            $client->srandmember($key);
        }

        return count($this->keys);
    }
}
