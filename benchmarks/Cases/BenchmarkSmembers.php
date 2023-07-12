<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkKeyCommand;

class BenchmarkSMEMBERS extends BenchmarkKeyCommand
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public static function flags(): int
    {
        return self::SET | self::READ;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function seed(): void
    {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $redis->sadd((string) $item['id'], array_keys($this->flattenArray($item)));
            $this->keys[] = $item['id'];
        }
    }
}
