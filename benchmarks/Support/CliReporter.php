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

    public function finishedSubject(Subject $subject)
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

    public function finishedSubjects(Subjects $subjects)
    {
        $benchmark = $subjects->benchmark;
        $subjects = $subjects->sortByTime();
        $baseMsMedian = $subjects[0]->msMedian();

        $headers = ['Client', 'Its', 'Revs', 'Operation', 'Time', 'rstdev', 'ops/s', 'Memory', 'Network', 'diff', 'diff'];
        $alignment = ['L', 'R', 'R', 'L', 'R', 'R', 'R', 'R', 'R', 'R', 'R'];
        $alignment = array_map(function ($v) { $v == 'L' ? STR_PAD_RIGHT : STR_PAD_LEFT; }, $alignment);

        $rows[] = $headers;

        foreach ($subjects as $i => $subject) {
            $msMedian = $subject->msMedian();
            $memoryMedian = $subject->memoryMedian();
            $bytesMedian = $subject->bytesMedian();
            $diff = -(1 - ($msMedian / $baseMsMedian)) * 100;
            $multiple = 1 / ($msMedian / $baseMsMedian);
            $rstdev = number_format($subject->msRstDev(), 2);
            $opsMedian = $subject->opsMedian();

            $rows[] = [
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
            ];
        }

        foreach ($rows as $row) {
            foreach ($row as $col => $val) {
                if ( ! isset($widths[$col]) || $widths[$col] < mb_strlen($val))
                    $widths[$col] = mb_strlen($val);
            }
        }

        $this->printHeader($widths, '-');
        $this->printRow(array_shift($rows), $widths, $alignment);
        $this->printHeader($widths, '-');

        foreach ($rows as $row) {
            $this->printRow($row, $widths, $alignment);
        }

        $this->printHeader($widths, '-');
    }

    protected function printRow($row, $widths, $alignment) {
        echo '| ';
        foreach ($row as $n => $col) {
            $this->printColumn($col, $widths[$n], $alignment[$n], $n == count($row) - 1);
        }
        echo "\n";
    }

    protected function printColumn($column, $width, $alignment, $tail) {
        echo $this->mb_str_pad($column, $width, ' ', $alignment);
        echo $tail ? ' |' : ' | ';
    }

    protected function printHeader($widths, $char) {
        echo "| ";
        foreach ($widths as $n => $width) {
            $this->printColumn(str_repeat($char, $width), $width, STR_PAD_LEFT, $n == count($widths) - 1);
        }
        echo "\n";
    }

    /* Stolen from the internet: https://stackoverflow.com/a/14773638/3605157 */
    protected function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT, $encoding = 'UTF-8')
    {
        $input_length = mb_strlen($input, $encoding);
        $pad_string_length = mb_strlen($pad_string, $encoding);

        if ($pad_length <= 0 || ($pad_length - $input_length) <= 0) {
            return $input;
        }

        $num_pad_chars = $pad_length - $input_length;

        switch ($pad_type) {
            case STR_PAD_RIGHT:
                $left_pad = 0;
                $right_pad = $num_pad_chars;
                break;

            case STR_PAD_LEFT:
                $left_pad = $num_pad_chars;
                $right_pad = 0;
                break;

            case STR_PAD_BOTH:
                $left_pad = floor($num_pad_chars / 2);
                $right_pad = $num_pad_chars - $left_pad;
                break;
        }

        $result = '';
        for ($i = 0; $i < $left_pad; ++$i) {
            $result .= mb_substr($pad_string, $i % $pad_string_length, 1, $encoding);
        }
        $result .= $input;
        for ($i = 0; $i < $right_pad; ++$i) {
            $result .= mb_substr($pad_string, $i % $pad_string_length, 1, $encoding);
        }

        return $result;
    }

}
