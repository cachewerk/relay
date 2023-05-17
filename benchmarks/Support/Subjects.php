<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class Subjects
{
    public Benchmark $benchmark;

    public array $subjects = [];

    public function __construct(Benchmark $benchmark)
    {
        $this->benchmark = $benchmark;
    }

    public function add(string $method)
    {
        $subject = new Subject($method, $this->benchmark);

        $this->subjects[] = $subject;

        return $subject;
    }

    public function sortByTime()
    {
        $subjects = $this->subjects;

        usort($subjects, fn ($a, $b) => $a->msMedian() < $b->msMedian() ? 1 : -1);

        return $subjects;
    }
}
