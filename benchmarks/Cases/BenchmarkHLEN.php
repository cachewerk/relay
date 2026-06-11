<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmarks\LenCommand;

class BenchmarkHLEN extends LenCommand
{
    public static function flags(): int
    {
        return self::HASH | self::READ;
    }
}
