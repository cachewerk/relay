<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use CacheWerk\Relay\Benchmarks\Support\Benchmarks\Benchmark;

abstract class Reporter
{
    protected bool $verbose;

    /**
     * @return void
     */
    public function __construct(bool $verbose)
    {
        $this->verbose = $verbose;
    }

    abstract public function startingBenchmark(Benchmark $benchmark, int $runs, float $duration, int $warmup, int $workers): void;

    abstract public function finishedIteration(Benchmark $benchmark, Iteration $iteration, string $client): void;

    abstract public function finishedSubject(Subject $subject): void;

    abstract public function finishedSubjects(Subjects $subjects, int $workers): void;

    /**
     * @param  int|float  $bytes
     * @return string
     */
    public static function humanMemory($bytes)
    {
        $i = (int) floor(log($bytes, 1024));

        return number_format(
            $bytes / pow(1024, $i),
            [0, 0, 2, 2][$i]
        ) . ['b', 'kb', 'mb', 'gb'][$i];
    }

    /**
     * @param  int|float  $number
     * @return string
     */
    public static function humanNumber($number)
    {
        $i = $number > 0 ? (int) floor(log($number, 1000)) : 0;

        return number_format(
            $number / pow(1000, $i),
            [0, 2, 2, 2][$i],
        ) . ['', 'K', 'M', 'G'][$i];
    }

    /**
     * @var array<string, true>
     */
    protected static array $warnedOnce = [];

    /**
     * @param  string  $fmt
     * @param  bool|float|int|string|null  ...$args
     * @return void
     */
    public static function printWarning(string $fmt, ...$args): void
    {
        fprintf(STDERR, "\n\033[33m WARNING \033[0m {$fmt}\n", ...$args);
    }

    /**
     * Print a warning only the first time a given message is encountered.
     *
     * @param  string  $fmt
     * @param  bool|float|int|string|null  ...$args
     * @return void
     */
    public static function printWarningOnce(string $fmt, ...$args): void
    {
        $message = sprintf($fmt, ...$args);

        if (isset(self::$warnedOnce[$message])) {
            return;
        }

        self::$warnedOnce[$message] = true;

        self::printWarning('%s', $message);
    }

    /**
     * @param  string  $fmt
     * @param  bool|float|int|string|null  ...$args
     * @return void
     */
    public static function printError(string $fmt, ...$args): void
    {
        fprintf(STDERR, "\n\033[41m ERROR \033[0m {$fmt}\n", ...$args);
    }
}
