<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Predis\Client as Predis;

class Runner
{
    protected string $host;

    protected int $port;

    /*
     * @var string|array<int, array<string>>|null
     */
    protected mixed $auth;

    protected Predis $redis;

    protected string $run_id;

    protected string $filter;

    protected int $runs;

    protected float $duration;

    protected int $warmup;

    /**
     * @param  string  $host
     * @param  string|int  $port
     * @param  string|array<int, array<string>>|null  $auth
     * @param  int  $runs
     * @param  int  $warmup
     * @return void
     */
    public function __construct($host, $port, $auth, $runs, float $duration, $warmup, string $filter)
    {
        $this->run_id = uniqid();

        $this->filter = $filter;

        $this->host = (string) $host;
        $this->port = (int) $port;
        $this->auth = empty($auth) ? null : $auth;

        $this->runs = $runs;
        $this->duration = $duration;
        $this->warmup = $warmup;

        $cpu = System::cpu();

        fprintf(STDERR, "Setting up on %s (%s cores/%s threads %s)\n", $cpu->type, $cpu->cores, $cpu->threads, $cpu->arch);

        fprintf(
            STDERR,
            "Using PHP %s (OPcache: %s, Xdebug: %s, New Relic: %s)\n",
            PHP_VERSION,
            $this->opcache() ? "\033[31mOn\033[0m" : 'Off',
            $this->xdebug() ? "\033[31mOn\033[0m" : 'Off',
            $this->newrelic() ? "\033[31mOn\033[0m" : 'Off'
        );

        $this->setUpRedis();

        fprintf(
            STDERR,
            "Connected to Redis (%s) at %s\n\n",
            $this->redis->info()['Server']['redis_version'],
            $this->port ? "tcp://{$host}:{$port}" : "unix:{$host}",
        );
    }

    protected function setUpRedis(): void
    {
        if (is_array($this->auth) && count($this->auth) == 2) {
            [$user, $pass] = $this->auth;
        } else {
            $user = null;
            $pass = $this->auth;
        }

        $parameters = [
            'host' => $this->host,
            'port' => $this->port,
            'username' => $user,
            'password' => $pass,
            'timeout' => 0.5,
            'read_write_timeout' => 0.5,
        ];

        if (! $this->port) {
            $parameters['scheme'] = 'unix';
            $parameters['path'] = $this->host;
        }

        $this->redis = new Predis($parameters, [
            'exceptions' => true,
        ]);
    }

    protected function resetStats(): void
    {
        $this->redis->config('RESETSTAT');

        if (function_exists('memory_reset_peak_usage')) {
            \memory_reset_peak_usage();
        }
    }

    /**
     * @return array<int, int>
     */
    protected function getNetworkStats(): array
    {
        $info = $this->redis->info('STATS')['Stats'];

        return [
            $info['total_net_input_bytes'],
            $info['total_net_output_bytes'],
        ];
    }

    protected function getRedisCommandCount(): int
    {
        $result = [];

        $stats = $this->redis->info('commandstats')['Commandstats'];

        foreach ($stats as $key => $val) {
            $cmd = strtoupper(str_replace('cmdstat_', '', $key));

            if (! preg_match('/calls=([0-9]+).*/', $val, $matches)) {
                continue;
            }

            $result[$cmd] = $matches[1];
        }

        return (int) array_sum($result);
    }

    protected function runMethod(Reporter $reporter, Subject $subject, Benchmark $benchmark, string $method): void
    {
        $benchmark->warmup($this->warmup, $method);

        for ($i = 0; $i < $this->runs; $i++) {
            $this->resetStats();

            $ops = 0;
            $cmds1 = $this->getRedisCommandCount();
            $t1 = microtime(true);

            do {
                $ops += $benchmark->{$method}();
                $t2 = microtime(true);
            } while ($t2 - $t1 < $this->duration);

            [$rx, $tx] = $this->getNetworkStats();
            $millis = ($t2 - $t1) * 1000;
            $memory = memory_get_peak_usage();
            $cmds = $this->getRedisCommandCount() - $cmds1;

            $iteration = $subject->addIteration($ops, $millis, $cmds, $memory, $rx, $tx);

            $reporter->finishedIteration($benchmark, $iteration, $subject->getClient());
        }

        $reporter->finishedSubject($subject);
    }

    /**
     * @param  class-string[]  $benchmarks
     */
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
                $this->runMethod($reporter, $subject, $benchmark, $method);
            }

            $reporter->finishedSubjects($subjects, 1);
        }
    }

    protected function opcache(): bool
    {
        return function_exists('opcache_get_status')
            && opcache_get_status();
    }

    protected function xdebug(): bool
    {
        return function_exists('xdebug_info')
            && ! in_array('off', xdebug_info('mode'));
    }

    protected function newrelic(): bool
    {
        return extension_loaded('newrelic')
            && ini_get('newrelic.enabled');
    }
}
