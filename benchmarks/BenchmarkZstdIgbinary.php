<?php

namespace CacheWerk\Relay\Benchmarks;

use Redis;
use Relay\Relay;

class BenchmarkZstdIgbinary extends Support\Benchmark
{
    protected int $chunkSize = 10;

    /**
     * @var array<int, object{id: string}>
     */
    protected array $data;

    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function getName(): string
    {
        return 'GET (Serialized)';
    }

    public static function flags(): int
    {
        return self::STRING | self::READ;
    }

    public function seedKeys(): void
    {
        $items = $this->randomItems();

        $this->seedClient($this->predis, serialize($items));
        $this->seedClient($this->phpredis, $items);
        $this->seedClient($this->relayNoCache, $items);
    }

    public function setUpClients(): void
    {
        parent::setUpClients();

        foreach ([$this->phpredis, $this->relayNoCache, $this->relay] as $client) {
            $client->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
            $client->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_ZSTD);
        }
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        $this->data = $this->loadJsonFile('meteorites.json', false);
        $this->keys = array_map(fn ($item) => $item->id, $this->data);

        $this->seedKeys();
    }

    /** @phpstan-ignore-next-line */
    protected function runBenchmark($client, bool $unserialize): int
    {
        $name = get_class($client);

        foreach ($this->keys as $key) {
            $v = $client->get("$name:$key");

            /* Predis does not have built-in serialization, so if we don't
             * unserialize there, it's not really a fair comparison, since
             * both PhpRedis and Relay will pay a price for deserialization.
             *
             * Note that this is still not really a fair comparison because
             * Relay and PhpRedis are decompressing and using a different
             * serializer. */
            if ($unserialize) {
                $v = unserialize($v);
            }
        }

        return count($this->keys);
    }

    public function benchmarkPredis(): int
    {
        return $this->runBenchmark($this->predis, true);
    }

    public function benchmarkPhpRedis(): int
    {
        return $this->runBenchmark($this->phpredis, false);
    }

    public function benchmarkRelayNoCache(): int
    {
        return $this->runBenchmark($this->relayNoCache, false);
    }

    public function benchmarkRelay(): int
    {
        return $this->runBenchmark($this->relay, false);
    }

    /** @phpstan-ignore-next-line */
    protected function seedClient($client, $items)
    {
        $name = get_class($client);

        foreach ($this->data as $item) {
            $client->set("$name:{$item->id}", $items);
        }
    }

    /**
     * @return array<int, object>
     */
    protected function randomItems()
    {
        return array_values(
            array_intersect_key(
                $this->data,
                array_flip(array_rand($this->data, $this->chunkSize)) // @phpstan-ignore-line
            )
        );
    }
}
