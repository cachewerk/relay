<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkSmismember extends Support\BenchmarkSetCommand {
    public function getName(): string {
        return 'SMISMEMBER';
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int {
        foreach ($this->keys as $key) {
            $client->smismember($key, $this->mems);
        }

        return count($this->keys);
    }

    public function benchmarkPredis(): int {
        foreach ($this->keys as $key) {
            /** @phpstan-ignore-next-line */
            $this->predis->smismember($key, ...$this->mems);
        }

        return count($this->keys);
    }

    public function benchmarkPhpRedis(): int {
        return $this->runBenchmark($this->phpredis);
    }

    public function benchmarkRelayNoCache(): int {
        return $this->runBenchmark($this->relayNoCache);
    }

    public function benchmarkRelay(): int {
        return $this->runBenchmark($this->relay);
    }
}
