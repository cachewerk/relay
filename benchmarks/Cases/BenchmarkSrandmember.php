<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkSetCommand;

class BenchmarkSrandmember extends BenchmarkSetCommand
{
    public function getName(): string
    {
        return 'SRANDMEMBER';
    }

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
