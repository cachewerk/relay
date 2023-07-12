<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkGetrange extends Support\BenchmarkStringRangeCommand
{
    public function getName(): string
    {
        return 'GETRANGE';
    }

    public function cmd(): string
    {
        return 'GETRANGE';
    }
}
