<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class Reporter
{
    abstract function startingBenchmark(Benchmark $benchmark);

    abstract function finishedIteration(Iteration $iteration);

    abstract function finishedSubject(Subject $subject);

    abstract function finishedSubjects(Subjects $subjects);

    protected function humanMemory($bytes)
    {
        $i = floor(log($bytes, 1024));

        return round(
            $bytes / pow(1024, $i),
            [0, 0, 2, 2, 3][$i]
        ) . ['b', 'kb', 'mb', 'gb'][$i];
    }

    protected function humanNumber($number)
    {
        $i = $number > 0 ? floor(log($number, 1000)) : 0;

        return number_format(
            $number / pow(1000, $i),
            [0, 2, 2, 2][$i], '.', ' '
        ) . ['', 'K', 'M', 'B'][$i];
    }
}
