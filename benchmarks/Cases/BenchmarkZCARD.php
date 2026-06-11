<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmarks\LenCommand;

class BenchmarkZCARD extends LenCommand
{
    public static function flags(): int
    {
        return self::ZSET | self::READ;
    }
}
