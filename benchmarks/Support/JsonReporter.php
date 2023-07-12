<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class JsonReporter extends CliReporter
{
    public function finishedSubjects(Subjects $subjects, int $workers): void
    {
        $report = [];
        $name = $subjects->benchmark->name();
        $subjects = $subjects->sortByOpsPerSec();

        if (empty($subjects)) {
            self::printError('No benchmarks were run! Please rerun with different options.');
            exit(1);
        }

        foreach ($subjects as $subject) {
            $opsPerWorker = $subject->opsPerSecMedian() / $workers;
            $rstdev = number_format($subject->opsPerSecRstDev(), 2);

            $report[] = [
                'client' => $subject->getClient(),
                'workers' => $workers,
                'memory' => $subject->memoryMedian(),
                'network_io' => $subject->bytesMedian(),
                'ops_per_sec' => round($subject->opsPerSecMedian(), 2),
                'ops_per_sec_per_worker' => round($opsPerWorker, 2),
                'rstdev' => $rstdev,
            ];
        }

        printf('%s', json_encode([
            'benchmark' => $name,
            'data' => $report,
        ], JSON_PRETTY_PRINT));
    }
}
