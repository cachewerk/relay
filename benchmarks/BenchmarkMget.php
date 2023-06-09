<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkMget extends Support\Benchmark
{
    const KeysPerCall = 8;

    /**
     * @var array<int, array<int, string>>
     */
    protected array $keyChunks;

    public function getName(): string {
        return 'MGET';
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        $keys = $this->loadJson('meteorites.json');
        $this->keyChunks = array_chunk($keys, self::KeysPerCall);
    }

    protected function runBenchmark($client): int {
        foreach ($this->keyChunks as $chunk) {
            $client->mget($chunk);
        }
        return count($this->keyChunks);
    }

    public function benchmarkPredis() {
        return $this->runBenchmark($this->predis);
    }

    public function benchmarkPhpRedis() {
        return $this->runBenchmark($this->phpredis);
    }

    public function benchmarkRelayNoCache() {
        return $this->runBenchmark($this->relayNoCache);
    }

    public function benchmarkRelay() {
        return $this->runBenchmark($this->relay);
    }
}
