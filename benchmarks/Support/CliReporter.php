<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class CliReporter extends Reporter
{
    public function startingBenchmark(Benchmark $benchmark)
    {
        printf(
            "\nExecuting %d iterations (%d warmup) of %s %s...\n\n",
            $benchmark->its(),
            $benchmark::Warmup ?? 'no',
            number_format($benchmark->opsTotal()),
            $benchmark::Name
        );
    }

    public function finishedIteration(Iteration $iteration)
    {
        $benchmark = $iteration->subject->benchmark;

        printf(
            "Executed %s %s using %s in %sms (%s ops/s) [memory:%s, network:%s]\n",
            number_format($benchmark->opsTotal()),
            $benchmark::Name,
            $iteration->subject->client(),
            number_format($iteration->ms, 2),
            $this->humanNumber($iteration->opsPerSec()),
            $this->humanMemory($iteration->memory),
            $this->humanMemory($iteration->bytesIn + $iteration->bytesOut)
        );
    }

    public function finishedSubject(Subject $subject)
    {
        $benchmark = $subject->benchmark;

        $ops = $benchmark::Operations;
        $its = $benchmark::Iterations;
        $revs = $benchmark::Revolutions;
        $name = $benchmark::Name;

        $ms_median = $subject->msMedian();
        $memory_median = $subject->memoryMedian();
        $bytes_median = $subject->bytesMedian();
        $rstdev = $subject->msRstDev();

        $ops_sec = ($ops * $revs) / ($ms_median / 1000);

        printf(
            "Executed %d iterations of %s %s using %s in ~%sms [±%.2f%%] (~%s ops/s) [memory:%s, network:%s]\n",
            count($subject->iterations),
            number_format($benchmark->opsTotal()),
            $name,
            $subject->client(),
            number_format($ms_median, 2),
            $rstdev,
            $this->humanNumber($ops_sec),
            $this->humanMemory($memory_median),
            $this->humanMemory($bytes_median * $its)
        );
    }

    public function finishedSubjects(Subjects $subjects)
    {
        $benchmark = $subjects->benchmark;
        $subjects = $subjects->sortByTime();
        $baseMsMedian = $subjects[0]->msMedian();

        $i = 0;

        echo "\n";
        $mask = "| %-16.16s | %4.4s | %4.4s | %-16.16s | %10.10s | %8.8s | %8.8s | %8.8s | %8.8s | %8.8s | %8.8s |\n";

        printf(
            $mask,
            'Client',
            'Its',
            'Revs',
            'Operation',
            'Time',
            'rstdev',
            'ops/s',
            'Memory',
            'Network',
            'diff',
            'diff'
        );

        printf($mask, ...array_fill(0, 20, str_repeat('-', 16)));

        foreach ($subjects as $subject) {
            $msMedian = $subject->msMedian();
            $memoryMedian = $subject->memoryMedian();
            $bytesMedian = $subject->bytesMedian();
            $diff = -(1 - ($msMedian / $baseMsMedian)) * 100;
            $multiple = 1 / ($msMedian / $baseMsMedian);
            $rstdev = number_format($subject->msRstDev(), 2);
            $opsMedian = $subject->opsMedian();

            printf(
                $mask,
                $subject->client(),
                $benchmark->its(),
                $benchmark->revs(),
                number_format($benchmark->opsTotal()) . ' ' . $benchmark::Name,
                number_format($msMedian, $msMedian > 999 ? 0 : 2) . ' ms',
                "±{$rstdev}%",
                $this->humanNumber($opsMedian),
                $this->humanMemory($memoryMedian),
                $this->humanMemory($bytesMedian),
                $i === 0 ? '1.0×' : number_format($multiple, $multiple < 2 ? 2 : 1) . '×',
                $i === 0 ? '0%' : number_format($diff, 1) . '%'
            );

            $i++;
        }

        printf($mask, ...array_fill(0, 20, str_repeat('-', 16)));
    }
}
