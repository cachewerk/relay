<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use ReflectionClass;

class JsonReporter extends CliReporter {
    public function finishedSubjects(Subjects $subjects, int $workers): void {
        $report = [];

        $name = $subjects->benchmark->getName();
        $subjects = $subjects->sortByOpsPerSec();
        $baseOpsPerSec = $subjects[0]->opsPerSecMedian();

        foreach ($subjects as $i => $subject) {
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

        printf("%s", json_encode(['benchmark' => $name, 'data' => $report], JSON_PRETTY_PRINT));
    }
}
