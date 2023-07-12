<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkKeyCommand;

class BenchmarkScard extends BenchmarkKeyCommand
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function getName(): string
    {
        return 'SCARD';
    }

    protected function cmd(): string
    {
        return 'SCARD';
    }

    public static function flags(): int
    {
        return self::SET | self::READ;
    }

    public function seedKeys(): void
    {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $redis->sadd((string) $item['id'], array_keys($this->flattenArray($item)));
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
