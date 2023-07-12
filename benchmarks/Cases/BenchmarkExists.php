<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkKeyCommand;

class BenchmarkEXISTS extends BenchmarkKeyCommand
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public static function flags(): int
    {
        return self::UTILITY | self::READ;
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
