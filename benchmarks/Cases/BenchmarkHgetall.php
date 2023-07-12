<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkKeyCommand;

class BenchmarkHGETALL extends BenchmarkKeyCommand
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public static function flags(): int
    {
        return self::HASH | self::READ;
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
            $redis->hmset((string) $item['id'], $this->flattenArray($item));
            $this->keys[] = $item['id'];
        }
    }
}
