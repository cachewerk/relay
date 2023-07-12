<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkKeyCommand;

class BenchmarkExists extends BenchmarkKeyCommand
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function getName(): string
    {
        return 'EXISTS';
    }

    public function cmd(): string
    {
        return 'EXISTS';
    }

    public static function flags(): int
    {
        return self::UTILITY | self::READ;
    }

    public function seedKeys(): void
    {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $redis->set((string) $item['id'], serialize($item));
            $this->keys[] = $item['id'];
        }
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seedKeys();
    }
}
