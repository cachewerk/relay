<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkLenCommand;

class BenchmarkLLEN extends BenchmarkLenCommand
{
    public static function flags(): int
    {
        return self::LIST | self::READ;
    }
}
