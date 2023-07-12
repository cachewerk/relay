<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkStringRangeCommand;

class BenchmarkBitcount extends BenchmarkStringRangeCommand
{
    public function getName(): string
    {
        return 'BITCOUNT';
    }

    public function cmd(): string
    {
        return 'BITCOUNT';
    }
}
