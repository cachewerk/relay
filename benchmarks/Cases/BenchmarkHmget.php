<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkHashCommand;

class BenchmarkHmget extends BenchmarkHashCommand
{
    const MemsPerCommand = 4;

    /**
     * @var array<int, string>
     */
    protected array $queryMems;

    public function getName(): string
    {
        return 'HMGET';
    }

    public static function flags(): int
    {
        return self::HASH | self::READ;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->queryMems = array_slice($this->mems, 0, self::MemsPerCommand);
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $key) {
            $client->hmget($key, $this->queryMems);
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
