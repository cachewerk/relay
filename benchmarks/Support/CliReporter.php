<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use ReflectionClass;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Output\StreamOutput;

class CliReporter extends Reporter
{
    public function startingBenchmark(Benchmark $benchmark, int $iterations, float $duration, int $warmup): void
    {
        fprintf(
            STDERR,
            "\n\033[30;42m %s \033[0m Executing %d iterations (%d warmup) for %2.2fs seconds\n\n",
            $benchmark->getName(),
            $iterations,
            $warmup,
            $duration
        );
    }

    public function finishedIteration(Benchmark $benchmark, Iteration $iteration, string $client): void
    {
        if (! $this->verbose) {
            return;
        }

        fprintf(
            STDERR,
            "Executed %s %s using %s in %sms (%s ops/sec) [memory: %s, network: %s]\n",
            number_format($iteration->ops),
            $benchmark->getName(),
            $client,
            number_format($iteration->ms, 2),
            CliReporter::humanNumber($iteration->opsPerSec()),
            CliReporter::humanMemory($iteration->memory),
            CliReporter::humanMemory($iteration->bytesIn + $iteration->bytesOut)
        );
    }

    public function finishedSubject(Subject $subject): void
    {
        if (! $this->verbose) {
            return;
        }

        $ms_median = $subject->msMedian();
        $memory_median = $subject->memoryMedian();
        $bytes_median = $subject->bytesMedian();
        $rstdev = $subject->msRstDev();
        $ops_sec = $subject->opsPerSecMedian();

        fprintf(
            STDERR,
            "Executed %s %s using %s in ~%sms [±%.2f%%] (~%s ops/s) [memory:%s, network:%s]\n\n",
            number_format($subject->opsTotal()),
            $subject->benchmark->getName(),
            $subject->getClient(),
            number_format($ms_median, 2),
            $rstdev,
            self::humanNumber($ops_sec),
            self::humanMemory($memory_median),
            self::humanMemory($bytes_median)
        );
    }

    public function finishedSubjects(Subjects $subjects, int $workers): void {
        $output = new StreamOutput(fopen('php://stdout', 'w')); // @phpstan-ignore-line

        $table = new Table($output);

        $table->setHeaders([
            'Workers', 'Client', 'Memory', 'Network', 'IOPS', 'IOPS/Worker', 'rstdev', 'Change', 'Factor',
        ]);

        $subjects = $subjects->sortByOpsPerSec();
        $baseOpsPerSec = $subjects[0]->opsPerSecMedian();

        $style_right = ['style' => new TableCellStyle(['align' => 'right'])];

        foreach ($subjects as $i => $subject) {
            $opsPerWorker = $subject->opsPerSecMedian() / $workers;
            $rstdev = number_format($subject->opsPerSecRstDev(), 2);
            $diff = -(1 - ($subject->opsPerSecMedian() / $baseOpsPerSec)) * 100;

            $factor = $i === 0 ? 1 : number_format($subject->opsPerSecMedian() / $baseOpsPerSec, 2);
            $change = $i === 0 ? 0 : number_format($diff, 2);

            $table->addRow([
                new TableCell((string)$workers, ['style' => new TableCellStyle(['align' => 'right'])]),
                $subject->getClient(),
                new TableCell(self::humanMemory($subject->memoryMedian()), $style_right),
                new TableCell(self::humanMemory($subject->bytesMedian()), $style_right),
                new TableCell(self::humanNumber($subject->opsPerSecMedian()), $style_right),
                new TableCell(self::humanNumber($opsPerWorker), $style_right),
                new TableCell("±{$rstdev}%", $style_right),
                new TableCell("{$change}%", $style_right),
                new TableCell("{$factor}", $style_right),
            ]);
        }

        $table->render();
    }
}
