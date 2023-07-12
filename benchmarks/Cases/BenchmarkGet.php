<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkKeyCommand;

class BenchmarkGET extends BenchmarkKeyCommand
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public static function flags(): int
    {
        return self::STRING | self::READ | self::DEFAULT;
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
            $redis->set((string) $item['id'], serialize($item));
            $this->keys[] = $item['id'];
        }
    }
}
