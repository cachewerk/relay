<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkStringRangeCommand;

class BenchmarkGetrange extends BenchmarkStringRangeCommand
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
