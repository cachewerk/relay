<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class Reporter
{
    protected bool $verbose;

    /**
     * @return void
     */
    public function __construct(bool $verbose)
    {
        $this->verbose = $verbose;
    }

    abstract function startingBenchmark(Benchmark $benchmark, int $runs, float $duration, int $warmup): void;

    abstract function finishedIteration(Benchmark $benchmark, Iteration $iteration, string $client): void;

    abstract function finishedSubject(Subject $subject): void;

    abstract function finishedSubjects(Subjects $subjects): void;

    /**
     * @param int|float $bytes
     * @return string
     */
    public static function humanMemory($bytes)
    {
        $i = floor(log($bytes, 1024));

        return number_format(
            $bytes / pow(1024, $i),
            [0, 0, 2, 2][$i]
        ) . ['b', 'kb', 'mb', 'gb'][$i];
    }

    /**
     * @param int|float $number
     * @return string
     */
    public static function humanNumber($number)
    {
        $i = $number > 0 ? floor(log($number, 1000)) : 0;

        return number_format(
            $number / pow(1000, $i),
            [0, 2, 2, 2][$i],
        ) . ['', 'K', 'M', 'B'][$i];
    }
}
