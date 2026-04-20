<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class BenchmarkInMemoryCommand extends Benchmark
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function setUp(): void
    {
        $this->setUpClients();
        $this->flush();

        if (method_exists($this, 'seed')) {
            $this->seed();
        }
    }

    protected function clients(): array
    {
        return array_filter([
            $this->table,
            $this->apcu,
        ]);
    }

    protected function flush(): void
    {
        foreach ($this->clients() as $client) {
            $client->clear();
        }
    }

    /**
     * @param  mixed  $client
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
