<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class Subject
{
    public string $method;

    public Benchmark $benchmark;

    /**
     * @var array<int, Iteration>
     */
    public array $iterations = [];

    public function __construct(string $method, Benchmark $benchmark)
    {
        $this->method = $method;
        $this->benchmark = $benchmark;
    }

    public function addIteration(float $ms, int $memory, int $bytesIn, int $bytesOut): Iteration
    {
        $iterations = new Iteration($ms, $memory, $bytesIn, $bytesOut, $this);

        $this->iterations[] = $iterations;

        return $iterations;
    }

    public function client(): string
    {
        return substr($this->method, 9);
    }

    /**
     * @return int|float
     */
    public function msMedian()
    {
        $times = array_map(function (Iteration $iteration) {
            return $iteration->ms;
        }, $this->iterations);

        return Statistics::median($times);
    }

    /**
     * @return int|float
     */
    public function msRstDev()
    {
        $times = array_map(function (Iteration $iteration) {
            return $iteration->ms;
        }, $this->iterations);

        return Statistics::rstdev($times);
    }

    /**
     * @return int|float
     */
    public function memoryMedian()
    {
        $times = array_map(function (Iteration $iteration) {
            return $iteration->memory;
        }, $this->iterations);

        return Statistics::median($times);
    }

    /**
     * @return int|float
     */
    public function bytesMedian()
    {
        $bytes = array_map(function (Iteration $iteration) {
            return $iteration->bytesIn + $iteration->bytesOut;
        }, $this->iterations);

        return Statistics::median($bytes);
    }

    /**
     * @return int|float
     */
    public function opsMedian()
    {
        $ops = array_map(function (Iteration $iteration) {
            return $iteration->opsPerSec();
        }, $this->iterations);

        return Statistics::median($ops);
    }
}
