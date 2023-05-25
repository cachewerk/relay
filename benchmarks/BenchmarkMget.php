<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkMget extends Support\Benchmark
{
    const Name = 'MGET';

    const Operations = 100;

    const Iterations = 5;

    const Revolutions = 500;

    const Warmup = 1;

    /**
     * @var array<int, array<int, string>>
     */
    protected array $keyChunks;

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        $keys = $this->loadJson('meteorites.json');
        $length = count($keys) / self::Operations;

        $this->keyChunks = array_chunk($keys, $length); // @phpstan-ignore-line
    }

    public function benchmarkPredis(): void
    {
        foreach ($this->keyChunks as $keys) {
            $this->predis->mget($keys);
        }
    }

    public function benchmarkPhpRedis(): void
    {
        foreach ($this->keyChunks as $keys) {
            $this->phpredis->mget($keys);
        }
    }

    public function benchmarkRelayNoCache(): void
    {
        foreach ($this->keyChunks as $keys) {
            $this->relayNoCache->mget($keys);
        }
    }

    public function benchmarkRelay(): void
    {
        foreach ($this->keyChunks as $keys) {
            $this->relay->mget($keys);
        }
    }
}
