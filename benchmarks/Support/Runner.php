<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Predis\Client as Predis;

class Runner
{
    protected string $host;

    protected int $port;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = (int) $port;

        $cpu = System::cpu();

        printf(
            "Setting up on %s (%s cores, %s)\n",
            $cpu->type,
            $cpu->cores,
            $cpu->arch
        );

        printf(
            "Using PHP %s (OPcache: %s, Xdebug: %s)\n",
            PHP_VERSION,
            opcache_get_status() ? 'On' : 'Off',
            function_exists('xdebug_info') && ! in_array('off', xdebug_info('mode')) ? 'On' : 'Off'
        );

        $redis = new Predis([
            'host' => $this->host,
            'port' => $this->port,
            'timeout' => 0.5,
            'read_write_timeout' => 0.5,
        ], [
            'exceptions' => true,
        ]);

        printf(
            "Connected to Redis (%s) at tcp://%s:%s\n\n",
            $redis->info()['Server']['redis_version'],
            $host,
            $port
        );
    }

    public function run(array $benchmarks)
    {
        foreach ($benchmarks as $class) {
            $benchmark = new $class($this->host, $this->port);
            $benchmark->setUp();

            $subjects = new Subjects($benchmark);

            $reporter = new CliReporter($benchmark);
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
                    memory_reset_peak_usage();
                    usleep(100000); // 100ms

                    $start = hrtime(true);

                    for ($r = 1; $r <= $benchmark::Revolutions; $r++) {
                        $benchmark->{$method}();
                    }

                    $end = hrtime(true);
                    $memory = memory_get_peak_usage();
                    $ms = ($end - $start) / 1e+6;

                    $iteration = $subject->addIteration($ms, $memory);

                    $reporter->finishedIteration($iteration);
                }

                $reporter->finishedSubject($subject);
            }

            $reporter->finishedSubjects($subjects);
        }
    }
}
