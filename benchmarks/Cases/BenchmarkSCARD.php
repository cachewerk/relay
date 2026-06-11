<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkLenCommand;

class BenchmarkSCARD extends BenchmarkLenCommand
{
    public static function flags(): int
    {
        return self::SET | self::READ;
    }
}
