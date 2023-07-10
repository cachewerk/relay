<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class BenchmarkKeyCommand extends Benchmark
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function getName(): string
    {
        return 'GET';
    }

    abstract protected function cmd(): string;

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seedKeys();
    }

    protected function runBenchmark($client): int
    {
        $cmd = $this->cmd();

        foreach ($this->keys as $key) {
            $client->{$cmd}($key);
        }

        return count($this->keys);
    }

    public function benchmarkPredis(): int
    {
        return $this->runBenchmark($this->predis);
    }

    public function benchmarkPhpRedis(): int
    {
        return $this->runBenchmark($this->phpredis);
    }

    public function benchmarkRelayNoCache(): int
    {
        return $this->runBenchmark($this->relayNoCache);
    }

    public function benchmarkRelay(): int
    {
        return $this->runBenchmark($this->relay);
    }
}
