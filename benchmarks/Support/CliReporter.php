<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use ReflectionClass;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Output\StreamOutput;

class CliReporter extends Reporter
{
    public function startingBenchmark(Benchmark $benchmark): void
    {
        $reflect = new ReflectionClass($benchmark);

        printf(
            "\n\033[30;42m %s \033[0m Executing %d iterations (%d warmup, %d revs) of %s %s operations...\n\n",
            substr(basename($reflect->getShortName()), 9),
            $benchmark->its(),
            $benchmark::Warmup ?? 'no',
            $benchmark->revs(),
            number_format($benchmark->opsTotal()),
            $benchmark::Name
        );
    }

    public function finishedIteration(Iteration $iteration): void
    {
        if (! $this->verbose) {
            return;
        }

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

    public function finishedSubject(Subject $subject): void
    {
        if (! $this->verbose) {
            return;
        }

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
            "Executed %d iterations of %s %s using %s in ~%sms [±%.2f%%] (~%s ops/s) [memory:%s, network:%s]\n\n",
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

    public function finishedSubjects(Subjects $subjects): void
    {
        $output = new StreamOutput(fopen('php://stdout', 'w')); // @phpstan-ignore-line

        $table = new Table($output);

        $table->setHeaders([
            'Client', 'Memory', 'Network',
            'IOPS', 'rstdev', 'Time',
            'Change', 'Factor',
        ]);

        $subjects = $subjects->sortByTime();
        $baseMsMedian = $subjects[0]->msMedian();

        $i = 0;

        foreach ($subjects as $subject) {
            $msMedian = $subject->msMedian();
            $memoryMedian = $subject->memoryMedian();
            $bytesMedian = $subject->bytesMedian();
            $diff = -(1 - ($msMedian / $baseMsMedian)) * 100;
            $multiple = 1 / ($msMedian / $baseMsMedian);
            $rstdev = number_format($subject->msRstDev(), 2);
            $opsMedian = $subject->opsMedian();

            $time = number_format($msMedian, 0);
            $factor = $i === 0 ? 1 : number_format($multiple, 2);
            $change = $i === 0 ? 0 : number_format($diff, 1);

            $table->addRow([
                $subject->client(),
                new TableCell($this->humanMemory($memoryMedian), ['style' => new TableCellStyle(['align' => 'right'])]),
                new TableCell($this->humanMemory($bytesMedian), ['style' => new TableCellStyle(['align' => 'right'])]),
                new TableCell($this->humanNumber($opsMedian), ['style' => new TableCellStyle(['align' => 'right'])]),
                new TableCell("±{$rstdev}%", ['style' => new TableCellStyle(['align' => 'right'])]),
                new TableCell("{$time}ms", ['style' => new TableCellStyle(['align' => 'right'])]),
                new TableCell("{$change}%", ['style' => new TableCellStyle(['align' => 'right'])]),
                new TableCell("{$factor}x", ['style' => new TableCellStyle(['align' => 'right'])]),
            ]);

            $i++;
        }

        $table->render();
    }
}
