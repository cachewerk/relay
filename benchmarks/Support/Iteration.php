<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class Iteration
{
    public int $ops;

    public float $ms;

    public int $redisCmds;

    public int $memory;

    public int $bytesIn;

    public int $bytesOut;

    public function __construct(int $ops, float $ms, int $redisCmds, int $memory, int $bytesIn, int $bytesOut)
    {
        $this->ops = $ops;
        $this->ms = $ms;
        $this->redisCmds = $redisCmds;
        $this->memory = $memory;
        $this->bytesIn = $bytesIn;
        $this->bytesOut = $bytesOut;
    }

    /**
     * @return int|float
     */
    public function opsPerSec()
    {
        return $this->ops / ($this->ms / 1000);
    }
}
