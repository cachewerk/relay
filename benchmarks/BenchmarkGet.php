<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkGet extends Support\BenchmarkKeyCommand
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function getName(): string
    {
        return 'GET';
    }

    protected function cmd(): string
    {
        return 'GET';
    }

    public static function flags(): int
    {
        return self::STRING | self::READ | self::DEFAULT;
    }

    public function seedKeys(): void
    {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
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
