<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkLenCommand;

class BenchmarkZCARD extends BenchmarkLenCommand
{
    public static function flags(): int
    {
        return self::ZSET | self::READ;
    }
}
