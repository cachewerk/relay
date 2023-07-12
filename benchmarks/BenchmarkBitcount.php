<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkBitcount extends Support\BenchmarkStringRangeCommand
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
