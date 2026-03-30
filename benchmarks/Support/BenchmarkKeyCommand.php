<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Relay\Relay;
use Predis\Client as Predis;

abstract class BenchmarkKeyCommand extends Benchmark
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        if (method_exists($this, 'seed')) {
            $this->seed();
        }
    }

    /**
     * @param  Relay|Predis|\Redis  $client
     */
    protected function runBenchmark($client): int
    {
        $cmd = $this->command();

        foreach ($this->keys as $key) {
            $client->{$cmd}($key);
        }

        return count($this->keys);
    }
}
