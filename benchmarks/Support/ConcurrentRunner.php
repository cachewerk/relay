<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class ConcurrentRunner extends Runner
{
    protected int $workers;

    /**
     * @param  string|array<int, array<string>>|null  $auth
     */
    public function __construct($host, $port, $auth, int $runs, float $duration, int $warmup, string $filter, int $workers)
    {
        parent::__construct($host, $port, $auth, $runs, $duration, $warmup, $filter);

        if ($workers < 2) {
            throw new \Exception("Invalid number of workers ({$workers} <= 1)\n");
        }

        $this->workers = $workers;
    }

    protected function saveOperations(string $method, int $operations, string $nonce): void
    {
        $this->redis->sadd(
            "benchmark_run:{$this->run_id}:{$method}:{$nonce}",
            [serialize([getmypid(), hrtime(true), $operations, \memory_get_peak_usage()])]
        );
    }

    /**
     * @return array<int, mixed>
     */
    protected function loadOperations(string $method, string $nonce): array
    {
        $result = [];

        foreach ($this->redis->smembers("benchmark_run:{$this->run_id}:{$method}:{$nonce}") as $iteration) {
            $result[] = unserialize($iteration);
        }

        return $result;
    }

    protected function blockForWorkers(string $nonce, float $timeout = 1.0): void
    {
        $waiting_key = "benchmark:spooling:{$this->run_id}:{$nonce}";

        /* Short circuit, if we're the last worker to spawn */
        if ($this->redis->incr($waiting_key) == $this->workers) {
            return;
        }

        /* Wait for all of the workers to be ready up to a predefined maximum time.
         * Because this is benchmarking code, we don't invoke the time() syscall each
         * iteration. */
        $st = microtime(true);
        for ($i = 1; $this->redis->get($waiting_key) < $this->workers; $i++) {
            if ($i % 10000 == 0 && ($et = microtime(true)) - $st >= $timeout) {
                fprintf(STDERR, "Error: Timed out waiting for %d workers to span (%2.2fs)\n",
                    $this->workers, $et - $st);
                exit(1);
            }
        }
    }

    protected function setConcurrentStart(string $nonce): void
    {
        $this->redis->setnx("benchmark:start:{$this->run_id}:{$nonce}", hrtime(true));
    }

    protected function getConcurrentStart(string $nonce): int
    {
        return (int) $this->redis->get("benchmark:start:{$this->run_id}:{$nonce}");
    }

    protected function runConcurrentOnce(Benchmark $benchmark, Reporter $reporter, Subject $subject, string $class, string $method): Iteration
    {
        $start = hrtime(true);
        [$rx1, $tx1] = $this->getNetworkStats();
        $cmd1 = $this->getRedisCommandCount();

        $pids = [];

        $nonce = uniqid();

        for ($i = 0; $i < $this->workers; $i++) {
            $pid = pcntl_fork();
            if ($pid < 0) {
                fprintf(STDERR, "Error: Cannot execute pcntl_fork()\n");
                exit(1);
            } elseif ($pid) {
                $pids[] = $pid;
            } else {
                $this->setUpRedis();
                $benchmark->refreshClients();

                /* Wait for workers to be ready */
                $this->blockForWorkers($nonce);
                $this->setConcurrentStart($nonce);

                /* Run operations */
                $operations = 0;
                do {
                    $operations += $benchmark->{$method}();
                } while ((hrtime(true) - $start) / 1e+9 < $this->duration);
                $this->saveOperations($method, $operations, $nonce);

                exit(0);
            }
        }

        /* Wait for workers to finish */
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status, WUNTRACED);
        }

        [$rx2, $tx2] = $this->getNetworkStats();
        $cmd2 = $this->getRedisCommandCount();

        $end = $max_mem = $tot_ops = 0;
        foreach ($this->loadOperations($method, $nonce) as [$pid, $now, $ops, $mem]) {
            $tot_ops += $ops;
            $max_mem = max($max_mem, $mem);
            $end = max($end, $now);
        }

        $start = $this->getConcurrentStart($nonce);

        /** @phpstan-ignore-next-line */
        return new Iteration($tot_ops, ($end - $start) / 1e+6, $cmd2 - $cmd1, $max_mem, $rx2 - $rx1, $tx2 - $tx1);
    }

    protected function runConcurrent(Reporter $reporter, Subject $subject, string $class, string $method): void
    {
        /** @var Benchmark $benchmark */
        $benchmark = new $class($this->host, $this->port, $this->auth);
        $benchmark->setUp();

        $benchmark->warmup($this->warmup, $method);

        for ($i = 0; $i < $this->runs; $i++) {
            $iteration = $this->runConcurrentOnce($benchmark, $reporter, $subject, $class, $method);
            $subject->addIterationObject($iteration);
            $reporter->finishedIteration($benchmark, $iteration, $subject->getClient());
        }

        $reporter->finishedSubject($subject);
    }

    public function run(array $benchmarks, Reporter $reporter): void
    {
        foreach ($benchmarks as $class) {
            /** @var Benchmark $benchmark */
            $benchmark = new $class($this->host, $this->port, $this->auth);
            $benchmark->setUp();

            $subjects = new Subjects($benchmark);

            $reporter->startingBenchmark($benchmark, $this->runs, $this->duration, $this->warmup);

            foreach ($benchmark->getBenchmarkMethods($this->filter) as $method) {
                $subject = $subjects->add($method);
                $this->runConcurrent($reporter, $subject, $class, $method);
            }

            $reporter->finishedSubjects($subjects, $this->workers);
        }
    }
}
