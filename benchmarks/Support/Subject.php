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

    public function addIterationObject(Iteration $iteration): void {
        $this->iterations[] = $iteration;
    }

    public function addIteration(int $ops, float $ms, int $redisCmds, int $memory, int $bytesIn, int $bytesOut): Iteration
    {
        $iteration = new Iteration($ops, $ms, $redisCmds, $memory, $bytesIn, $bytesOut);
        $this->addIterationObject($iteration);
        return $iteration;
    }

    public function getClient(): string
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
    public function opsPerSecRstDev() {
        $ops = array_map(function (Iteration $iteration) {
            return $iteration->opsPerSec();
        }, $this->iterations);

        return Statistics::rstdev($ops);
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
    public function opsPerSecMedian()
    {
        $ops = array_map(function (Iteration $iteration) {
            return $iteration->opsPerSec();
        }, $this->iterations);

        return Statistics::median($ops);
    }

    /**
     * @return int|float
     */
    public function opsBase() {
        $ops = array_map(function (Iteration $iteration) {
            return $iteration->opsPerSec();
        }, $this->iterations);

        return min($ops);
    }

    public function opsTotal(): int {
        $ops = array_map(function (Iteration $iteration) {
            return $iteration->ops;
        }, $this->iterations);

        return array_sum($ops);
    }

}
