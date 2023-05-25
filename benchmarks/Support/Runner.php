<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Predis\Client as Predis;

class Runner
{
    protected string $host;

    protected int $port;

    protected ?string $auth;

    protected bool $socket;

    protected bool $verbose = false;

    protected Predis $redis;

    public function __construct($host, $port, $auth, bool $socket, bool $verbose)
    {
        $this->socket = $socket;
        $this->verbose = $verbose;

        $this->host = (string) $host;
        $this->port = (int) $port;
        $this->auth = empty($auth) ? null : $auth;

        $cpu = System::cpu();

        printf(
            "Setting up on %s (%s cores, %s)\n",
            $cpu->type,
            $cpu->cores,
            $cpu->arch
        );

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
            $this->socket ? "unix:{$host}" : "tcp://{$host}:{$port}",
        );
    }

    protected function setUpRedis()
    {
        $parameters = [
            'host' => $this->host,
            'port' => $this->port,
            'password' => $this->auth,
            'timeout' => 0.5,
            'read_write_timeout' => 0.5,
        ];

        if ($this->socket) {
            $parameters['scheme'] = 'unix';
            $parameters['path'] = $this->host;
        }

        $this->redis = new Predis($parameters, [
            'exceptions' => true,
        ]);
    }

    public function run(array $benchmarks)
    {
        foreach ($benchmarks as $class) {
            $benchmark = new $class($this->host, $this->port, $this->auth, $this->socket);
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

    protected function opcache()
    {
        return function_exists('opcache_get_status')
            && opcache_get_status();
    }

    protected function xdebug()
    {
        return function_exists('xdebug_info')
            && ! in_array('off', xdebug_info('mode'));
    }

    protected function newrelic()
    {
        return extension_loaded('newrelic')
            && ini_get('newrelic.enabled');
    }
}
