<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Predis\Client as Predis;

class Runner
{
    protected string $host;

    protected int $port;

    protected ?string $auth;

    protected bool $verbose = false;

    protected Predis $redis;

    /**
     * @param string $host
     * @param string|int $port
     * @param ?string $auth
     * @param bool $verbose
     * @return void
     */
    public function __construct($host, $port, $auth, bool $verbose)
    {
        $this->verbose = $verbose;

        $this->host = (string) $host;
        $this->port = (int) $port;
        $this->auth = empty($auth) ? null : $auth;

        /** @var object{type: string, cores: int, arch: string} $cpu */
        $cpu = System::cpu();

        printf("Setting up on %s (%s cores, %s)\n", $cpu->type, $cpu->cores, $cpu->arch);

        printf(
            "Using PHP %s (OPcache: %s, Xdebug: %s, New Relic: %s)\n",
            PHP_VERSION,
            $this->opcache() ? "\033[31mOn\033[0m" : "Off",
            $this->xdebug() ? "\033[31mOn\033[0m" : "Off",
            $this->newrelic() ? "\033[31mOn\033[0m" : 'Off'
        );

        $this->setUpRedis();

        printf(
            "Connected to Redis (%s) at %s\n\n",
            $this->redis->info()['Server']['redis_version'],
            $this->port ? "tcp://{$host}:{$port}" : "unix:{$host}",
        );
    }

    protected function setUpRedis(): void
    {
        $parameters = [
            'host' => $this->host,
            'port' => $this->port,
            'password' => $this->auth,
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

    /**
     * @param class-string[] $benchmarks
     * @return void
     */
    public function run(array $benchmarks): void
    {
        foreach ($benchmarks as $class) {
            /** @var Benchmark $benchmark */
            $benchmark = new $class($this->host, $this->port, $this->auth);
            $benchmark->setUp();

            $subjects = new Subjects($benchmark);

            $reporter = new CliReporter($this->verbose);
            $reporter->startingBenchmark($benchmark);

            $methods = array_filter(
                get_class_methods($benchmark),
                fn ($method) => str_starts_with($method, 'benchmark')
            );

            foreach ($methods as $method) {
                $subject = $subjects->add($method);

                usleep(500000); // 500ms

                for ($i = 0; $i < $benchmark::Warmup; $i++) {
                    for ($i = 1; $i <= $benchmark::Revolutions; $i++) {
                        $benchmark->{$method}();
                    }
                }

                for ($i = 0; $i < $benchmark::Iterations; $i++) {
                    $this->redis->config('RESETSTAT');
                    if (function_exists('memory_reset_peak_usage')) {
                        memory_reset_peak_usage();
                    }

                    usleep(100000); // 100ms

                    $start = hrtime(true);

                    for ($r = 1; $r <= $benchmark::Revolutions; $r++) {
                        $benchmark->{$method}();
                    }

                    $end = hrtime(true);
                    $memory = memory_get_peak_usage();
                    $ms = ($end - $start) / 1e+6;

                    $usage = $this->redis->info('STATS')['Stats'];
                    $bytesIn = $usage['total_net_input_bytes'];
                    $bytesOut = $usage['total_net_output_bytes'];

                    $iteration = $subject->addIteration($ms, $memory, $bytesIn, $bytesOut);

                    $reporter->finishedIteration($iteration);
                }

                $reporter->finishedSubject($subject);
            }

            $reporter->finishedSubjects($subjects);
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
