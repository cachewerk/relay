<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkSinter extends Benchmark
{
    const KeysPerCall = 8;

    /**
     * @var array<int, array<int, string>>
     */
    protected array $keyChunks;

    public function getName(): string
    {
        return 'SINTER';
    }

    public static function flags(): int
    {
        return self::SET | self::READ;
    }

    public function warmup(int $times, string $method): void
    {
        if ($times == 0) {
            return;
        }

        parent::warmup($times, $method);

        foreach ($this->keyChunks as $chunk) {
            foreach ($chunk as $key) {
                $this->relay->smembers((string) $key);
            }
        }
    }

    public function seedKeys(): void
    {
        $keys = [];

        $redis = $this->createPredis();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $redis->sadd((string) $item['id'], array_keys($this->flattenArray($item)));
            $keys[] = $item['id'];
        }

        $this->keyChunks = array_chunk($keys, self::KeysPerCall);
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seedKeys();
    }

    protected function runBenchmark($client): int
    {
        foreach ($this->keyChunks as $chunk) {
            $client->sinter($chunk);
        }

        return count($this->keyChunks);
    }
}
