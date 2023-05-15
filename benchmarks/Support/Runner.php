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
            opcache_get_status() ? '????' : '???'
        );

        $redis = new Predis([
            'host' => $this->host,
            'port' => $this->port,
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

    public function run(array $benchmarks, int $iterations)
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

                usleep(500000); // 500ms

                for ($i = 0; $i < $benchmark::Warmup; $i++) {
                    $benchmark->{$method}();
                }

                for ($i = 0; $i < $iterations; $i++) {
                    memory_reset_peak_usage();
                    usleep(100000); // 100ms

                    $start = hrtime(true);

                    // TODO: add revolutions...
                    $benchmark->{$method}();

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
        $its = $results['its'];
        $iterations = $results['iterations'];

        $ops = $results['ops'];

        $times = array_column($iterations, 'ms');
        $ms_median = Statistics::median($times);

        $bytes = array_column($iterations, 'mem');
        $memory_median = Statistics::median($bytes);

        $rstdev = Statistics::rstdev($times);

        printf(
            "Executed %d iterations of %s %s using %s in %sms (%s/sec) consuming %s [±%.2f%%]\n",
            $its,
            number_format($ops),
            $results['name'],
            $results['client'],
            number_format($ms_median, 2),
            number_format($ops / ($ms_median / 1000)),
            $this->humanMemory($memory_median),
            $rstdev
        );
    }
}
