<?php

namespace CacheWerk\Relay\Benchmarks\Support;

// use Predis\Client as Predis;
use CacheWerk\Relay\Benchmarks\Support\RawIteration;

class ConcurrentRunner extends Runner {
    protected int $workers;

    public function __construct($host, $port, $auth, string $filter, bool $verbose, int $workers, float $duration,
                                int $warmup)
    {
        parent::__construct($host, $port, $auth, 1, $duration, $warmup, $filter, $verbose);

        if ($workers < 2) {
            throw new \Exception("Invalid number of workers ($workers <= 1)\n");
        }

        $this->workers = $workers;
    }

    protected function saveOperations($method, $operations) {
        $this->redis->sadd(
            "benchmark_run:{$this->run_id}:$method",
            serialize([getmypid(), hrtime(true), $operations, \memory_get_peak_usage()])
        );
    }

    protected function loadOperations($method) {
        $result = [];

        foreach ($this->redis->smembers("benchmark_run:{$this->run_id}:$method") as $iteration) {
            $result[] = unserialize($iteration);
        }

        return $result;
    }

    protected function blockForWorkers($timeout = 1.0) {
        $waiting_key = "benchmark:spooling:{$this->run_id}";

        /* Short circuit, if we're the last worker to spawn */
        if ($this->redis->incr($waiting_key) == $this->workers)
            return;

        /* Wait for all of the workers to be ready up to a predefined maximum time.
           Because this is benchmarking code, we don't invoke the time() syscall each
           iteration. */
        $st = microtime(true);
        for ($i = 1; $this->redis->get($waiting_key) < $this->workers; $i++) {
            if ($i % 10000 == 0 && ($et = microtime(true)) - $st >= $timeout) {
                fprintf(STDERR, "Error:  Timed out waiting for %d workers to span (%2.2fs)\n",
                        $this->workers, $et - $st);
                exit(1);
            }
        }
    }

    protected function setConcurrentStart() {
        $this->redis->setnx("benchmark:start:{$this->run_id}", hrtime(true));
    }

    protected function getConcurrentStart() {
        return $this->redis->get("benchmark:start:{$this->run_id}");
    }

    protected function runConcurrent($reporter, $subject, $class, $method) {
        /* Warm up once, outside of the forked workers */
        $benchmark = new $class($this->host, $this->port, $this->auth);
        $benchmark->setUp();

        for ($i = 0; $i < $this->warmup; $i++) {
            $benchmark->{$method}();
        }

        $start = hrtime(true);
        list($rx1, $tx1) = $this->getNetworkStats();
        $cmd1 = $this->getRedisCommandCount();

        $pids = [];

        for ($i = 0; $i < $this->workers; $i++) {
            $pid = pcntl_fork();
            if ($pid < 0) {
                fprintf(STDERR, "Error:  Cannot execute pcntl_fork()!\n");
                exit(1);
            } else if ($pid) {
                $pids[] = $pid;
            } else {
                /* Refresh clients since we're in a forked child process.
                   NOTE:  Not required for Relay but for the others it is. */
                $this->setUpRedis();
                $benchmark->setUpClients();

                /* Wait for workers to be ready */
                $this->blockForWorkers();
                $this->setConcurrentStart();

                /* Run operations */
                $operations = 0;
                do {
                    $operations += $benchmark->{$method}();
                } while ((hrtime(true) - $start) / 1e+9 < $this->duration);
                $this->saveOperations($method, $operations);

                exit(0);
            }
        }

        /* Wait for workers to finish */
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status, WUNTRACED);
        }

        list($rx2, $tx2) = $this->getNetworkStats();
        $cmd2 = $this->getRedisCommandCount();

        $end = $max_mem = $tot_ops = 0;
        foreach ($this->loadOperations($method) as [$pid, $now, $ops, $mem]) {
            $tot_ops += $ops;
            $max_mem = max($max_mem, $mem);
            $end = max($end, $now);
        }

        $start = $this->getConcurrentStart();

        $iteration = $subject->addIteration($tot_ops, ($end - $start) / 1e+6, $cmd2 - $cmd1, $max_mem, $rx2 - $rx1, $tx2 - $tx1);
        $reporter->finishedIteration($benchmark, $iteration, $subject->getClient());
        $reporter->finishedSubject($subject);
    }

    public function run(array $benchmarks): void
    {
        foreach ($benchmarks as $class) {
            $benchmark = new $class($this->host, $this->port, $this->auth);
            $benchmark->setUp();

            $subjects = new Subjects($benchmark);
            $reporter = new CliReporter($this->verbose);

            $reporter->startingBenchmark($benchmark, $this->runs, $this->duration, $this->warmup);

            foreach ($benchmark->getBenchmarkMethods($this->filter) as $method) {
                $subject = $subjects->add($method);
                $this->runConcurrent($reporter, $subject, $class, $method);
            }

            $reporter->finishedSubjectsConcurrent($subjects, $this->workers);
        }
    }
}
