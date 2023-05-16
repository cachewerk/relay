<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class CliReporter extends Reporter
{
    public function startingBenchmark(Benchmark $benchmark)
    {
        $ops = $benchmark::Operations;
        $revs = $benchmark::Revolutions;

        printf(
            "Executing %d iterations (%d warmup) of %s %s...\n",
            $benchmark::Iterations,
            $benchmark::Warmup ?? 'no',
            number_format($ops * $revs),
            $benchmark::Name
        );
    }

    public function finishedIteration(Iteration $iteration)
    {
        $benchmark = $iteration->subject->benchmark;

        $ops = $benchmark::Operations;
        $revs = $benchmark::Revolutions;

        $ops_sec = ($ops * $revs) / ($iteration->ms / 1000);

        printf(
            "Executed %s %s using %s in %sms (%s ops/s) consuming %s\n",
            number_format($ops * $revs),
            $benchmark::Name,
            $iteration->subject->client(),
            number_format($iteration->ms, 2),
            $this->humanNumber($ops_sec),
            $this->humanMemory($iteration->memory)
        );
    }

    public function finishedSubject(Subject $subject)
    {
        $benchmark = $subject->benchmark;

        $ops = $benchmark::Operations;
        $revs = $benchmark::Revolutions;
        $name = $benchmark::Name;

        $ms_median = $subject->msMedian();
        $memory_median = $subject->memoryMedian();
        $rstdev = $subject->msRstDev();

        $ops_sec = ($ops * $revs) / ($ms_median / 1000);

        printf(
            "Executed %d iterations of %s %s using %s in %sms (%s ops/s) consuming %s [±%.2f%%]\n",
            count($subject->iterations),
            number_format($ops * $revs),
            $name,
            $subject->client(),
            number_format($ms_median, 2),
            $this->humanNumber($ops_sec),
            $this->humanMemory($memory_median),
            $rstdev
        );
    }

    public function finishedSubjects(Subjects $subjects)
    {
        $subjects = $subjects->sortByTime();
        $baseMsMedian = $subjects[0]->msMedian();

        echo PHP_EOL;

        $i = 0;

        foreach ($subjects as $subject) {
            $msMedian = $subject->msMedian();
            $diff = (1 - ($msMedian / $baseMsMedian)) * 100;

            printf(
                "%s (%sms) [%.2fx, %s%%]\n",
                $subject->client(),
                number_format($msMedian, 2),
                1 / ($msMedian / $baseMsMedian),
                $i === 0 ? 0 : number_format($diff, 1),
            );

            $i++;
        }
    }
}
