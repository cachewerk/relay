<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Exception;
use Predis\Client as Predis;
use Throwable;

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
            false ? 'TODO' : 'TODO'
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

            $methods = array_filter(
                get_class_methods($benchmark),
                fn ($method) => str_starts_with($method, 'benchmark')
            );

            $results = [];

            $ops = $benchmark::Operations;
            $iterations = $benchmark::Iterations;
            $revolutions = $benchmark::Revolutions;

            printf(
                "Executing %d iterations (%d warmup) of %s %s...\n",
                $iterations,
                $benchmark::Warmup ?? 'no',
                number_format($ops),
                $benchmark::Name
            );

            foreach ($methods as $method) {
                $client = substr($method, 9);

                $results[$class][$method]['name'] = $benchmark::Name;
                $results[$class][$method]['client'] = $client;
                $results[$class][$method]['ops'] = $ops;
                $results[$class][$method]['its'] = $iterations;
                $results[$class][$method]['revs'] = $revolutions;

                usleep(500000); // 500ms

                for ($i = 0; $i < $benchmark::Warmup; $i++) {
                    for ($i = 1; $i <= $revolutions; $i++) {
                        $benchmark->{$method}();
                    }
                }

                for ($i = 0; $i < $iterations; $i++) {
                    memory_reset_peak_usage();
                    usleep(100000); // 100ms

                    $start = hrtime(true);

                    for ($r = 1; $r <= $revolutions; $r++) {
                        $benchmark->{$method}();
                    }

                    $end = hrtime(true);
                    $memory = memory_get_peak_usage();
                    $ms = ($end - $start) / 1e+6;

                    $results[$class][$method]['iterations'][$i] = [
                        'ms' => $ms,
                        'mem' => $memory,
                    ];

                    // printf(
                    //     "Executed %s %s using %s in %sms (%s/sec) consuming %s\n",
                    //     number_format($ops),
                    //     $benchmark::Name,
                    //     $client,
                    //     number_format($ms, 2),
                    //     number_format($ops / ($ms / 1000)),
                    //     $this->humanMemory($memory)
                    // );
                }

                $this->resultSummary($results[$class][$method]);
            }
        }
    }

    function humanMemory($bytes) {
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), [0, 0, 2, 2, 3][$i]) . ['b', 'kb', 'mb', 'gb'][$i];
    }

    function resultSummary($results)
    {
        $iterations = $results['iterations'];

        $ops = $results['ops'];
        $revs = $results['revs'];

        $times = array_column($iterations, 'ms');
        $ms_median = Statistics::median($times);

        $bytes = array_column($iterations, 'mem');
        $memory_median = Statistics::median($bytes);

        $rstdev = Statistics::rstdev($times);

        $ops_sec = ($ops * $revs) / ($ms_median / 1000);

        printf(
            "Executed %d iterations of %s %s using %s in %sms (%s/sec) consuming %s [±%.5f%%]\n",
            count($results['iterations']),
            number_format($ops * $revs),
            $results['name'],
            $results['client'],
            number_format($ms_median, 2),
            number_format($ops_sec),
            $this->humanMemory($memory_median),
            $rstdev
        );
    }
}
