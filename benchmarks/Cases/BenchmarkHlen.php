<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkLenCommand;

class BenchmarkHLEN extends BenchmarkLenCommand
{
    public static function flags(): int
    {
        return self::HASH | self::READ;
    }
}
