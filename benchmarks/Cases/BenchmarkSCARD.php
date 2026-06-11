<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmarks\LenCommand;

class BenchmarkSCARD extends LenCommand
{
    public static function flags(): int
    {
        return self::SET | self::READ;
    }
}
