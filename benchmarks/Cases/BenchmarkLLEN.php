<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmarks\LenCommand;

class BenchmarkLLEN extends LenCommand
{
    public static function flags(): int
    {
        return self::LIST | self::READ;
    }
}
