<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkLenCommand;

class BenchmarkSTRLEN extends BenchmarkLenCommand
{
    public static function flags(): int
    {
        return self::STRING | self::READ;
    }
}
