<?php

namespace CacheWerk\Relay\Benchmarks;

class BenchmarkHgetall extends Support\BenchmarkKeyCommand {
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function getName(): string {
        return 'HGETALL';
    }

    public function cmd(): string {
        return 'HGETALL';
    }

    public static function flags(): int {
        return self::HASH | self::READ;
    }

    public function seedKeys(): void {
        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json', true) as $item) {
            $redis->hmset((string)$item['id'], $this->flattenArray($item));
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
