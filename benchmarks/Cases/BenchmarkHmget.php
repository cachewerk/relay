<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use Redis as PhpRedis;
use Relay\Relay;
use Predis\Client as Predis;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkHashCommand;

class BenchmarkHMGET extends BenchmarkHashCommand
{
    const MemsPerCommand = 4;

    /**
     * @var array<int, string>
     */
    protected array $queryMems;

    public static function flags(): int
    {
        return self::HASH | self::READ;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->queryMems = array_slice($this->mems, 0, self::MemsPerCommand);
    }

    /**
     * @param  PhpRedis|Relay|Predis  $client
     * @return int
     */
    protected function runBenchmark($client): int
    {
        foreach ($this->keys as $key) {
            $client->hmget($key, $this->queryMems);
        }

        return count($this->keys);
    }
}
