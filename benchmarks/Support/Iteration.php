<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class Iteration
{
    public float $ms;

    public int $memory;

    public Subject $subject;

    public function __construct(float $ms, int $memory, Subject $subject)
    {
        $this->ms = $ms;
        $this->memory = $memory;
        $this->subject = $subject;
    }
}
