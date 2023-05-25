<?php

namespace CacheWerk\Relay\Benchmarks;

use Redis;
use Relay\Relay;

class BenchmarkZstdIgbinary extends Support\Benchmark
{
    const Name = 'GET';

    const Operations = 1000;

    const Iterations = 5;

    const Revolutions = 25;

    const Warmup = 1;

    protected int $chunkSize = 10;

    /**
     * @var array<int, object>
     */
    protected array $data;

    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        $json = file_get_contents(__DIR__ . '/Support/data/meteorites.json');

        $this->data = json_decode($json, false, 512, JSON_THROW_ON_ERROR);
        $this->keys = array_map(fn ($item) => $item->id, $this->data);

        $this->seedRelay();
        $this->seedPredis();
        $this->seedPhpRedis();
    }

    public function benchmarkPredis(): void
    {
        foreach ($this->keys as $key) {
            $value = $this->predis->get("predis:{$key}");
        }
    }

    public function benchmarkPhpRedis(): void
    {
        foreach ($this->keys as $key) {
            $value = $this->phpredis->get("phpredis:{$key}");
        }
    }

    public function benchmarkRelayNoCache(): void
    {
        foreach ($this->keys as $key) {
            $value = $this->relayNoCache->get("relay:{$key}");
        }
    }

    public function benchmarkRelay(): void
    {
        foreach ($this->keys as $key) {
            $value = $this->relay->get("relay:{$key}");
        }
    }

    protected function seedPredis(): void
    {
        foreach ($this->data as $item) {
            $this->predis->set("predis:{$item->id}", serialize($this->randomItems()));
        }
    }

    protected function seedPhpRedis(): void
    {
        $this->phpredis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
        $this->phpredis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_ZSTD);

        foreach ($this->data as $item) {
            $this->phpredis->set("phpredis:{$item->id}", $this->randomItems());
        }
    }

    protected function seedRelay(): void
    {
        $this->relayNoCache->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_IGBINARY);
        $this->relayNoCache->setOption(Relay::OPT_COMPRESSION, Relay::COMPRESSION_ZSTD);

        $this->relay->setOption(Relay::OPT_SERIALIZER, Relay::SERIALIZER_IGBINARY);
        $this->relay->setOption(Relay::OPT_COMPRESSION, Relay::COMPRESSION_ZSTD);

        foreach ($this->data as $item) {
            $this->relayNoCache->set("relay:{$item->id}", $this->randomItems());
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
                array_flip(array_rand($this->data, $this->chunkSize))
            )
        );
    }
}
