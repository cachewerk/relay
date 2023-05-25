<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class Subjects
{
    public Benchmark $benchmark;

    /**
     * @var array<int, Subject>
     */
    public array $subjects = [];

    public function __construct(Benchmark $benchmark)
    {
        $this->benchmark = $benchmark;
    }

    public function add(string $method): Subject
    {
        $subject = new Subject($method, $this->benchmark);

        $this->subjects[] = $subject;

        return $subject;
    }

    /**
     * @return array<int, Subject>
     */
    public function sortByTime()
    {
        $subjects = $this->subjects;

        usort($subjects, fn ($a, $b) => $a->msMedian() < $b->msMedian() ? 1 : -1);

        return $subjects;
    }
}
