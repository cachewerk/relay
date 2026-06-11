<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use Redis as PhpRedis;
use Relay\Relay;
use Predis\Client as Predis;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkHashCommand;

class BenchmarkHGET extends BenchmarkHashCommand
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
        foreach ($this->keys as $i => $key) {
            $client->hget($key, $this->queryMems[$i % count($this->queryMems)]);
        }

        return count($this->keys);
    }
}
