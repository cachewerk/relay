<?php

namespace CacheWerk\Relay\Benchmarks\Support\Benchmarks;

use Redis as PhpRedis;

use Relay\Relay;
use Predis\Client as Predis;

use CacheWerk\Relay\Benchmarks\Support\Clients\InMemoryClient;

abstract class KeyCommand extends Benchmark
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
     * @param  Relay|PhpRedis|Predis|InMemoryClient  $client
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
