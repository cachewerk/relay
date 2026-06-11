<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmarks\LenCommand;

class BenchmarkSTRLEN extends LenCommand
{
    public static function flags(): int
    {
        return self::STRING | self::READ;
    }
}
