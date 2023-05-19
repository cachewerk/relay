<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class Iteration
{
    public float $ms;

    public int $memory;

    public int $bytesIn;

    public int $bytesOut;

    public Subject $subject;

    public function __construct(float $ms, int $memory, int $bytesIn, int $bytesOut, Subject $subject)
    {
        $this->ms = $ms;
        $this->memory = $memory;
        $this->bytesIn = $bytesIn;
        $this->bytesOut = $bytesOut;

        $this->subject = $subject;
    }

    public function opsPerSec()
    {
        $benchmark = $this->subject->benchmark;

        return $benchmark->opsTotal() / ($this->ms / 1000);
    }
}
