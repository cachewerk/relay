<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkSismember extends Support\BenchmarkSetCommand
{
    public function getName(): string
    {
        return 'SISMEMBER';
    }

    public static function flags(): int
    {
        return self::SET | self::READ;
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $key) {
            foreach ($this->mems as $mem) {
                $client->sismember($key, $mem);
            }
        }

        return count($this->keys) * count($this->mems);
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
