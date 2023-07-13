<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use Relay\Relay;

use CacheWerk\Relay\Benchmarks\Support\Reporter;
use CacheWerk\Relay\Benchmarks\Support\Benchmark;

class BenchmarkZstdIgbinary extends Benchmark
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

    public function name(): string
    {
        return 'GET (Compressed)';
    }

    public static function flags(): int
    {
        return self::STRING | self::READ | self::DEFAULT;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();

        $this->data = $this->loadJsonFile('meteorites.json', false);
        $this->keys = array_map(fn ($item) => $item->id, $this->data);

        $this->seed();
    }

    public function seed(): void
    {
        $items = $this->randomItems();

        $this->seedClient($this->predis, $items, true);
        $this->seedClient($this->relayNoCache, $items);

        if (extension_loaded('redis')) {
            $this->seedClient($this->phpredis, $items);
        }
    }

    /**
     * @param  \Redis|\Relay\Relay  $client
     * @return void
     */
    protected function setSerialization($client): void
    {
        if (! $client->setOption($client::OPT_SERIALIZER, $client::SERIALIZER_IGBINARY)) {
            Reporter::printWarning(sprintf('Unable to set igbinary serializer on %s', get_class($client)));
        }

        if (! $client->setOption($client::OPT_COMPRESSION, $client::COMPRESSION_ZSTD)) {
            Reporter::printWarning(sprintf('Unable to set zstd compression on %s', get_class($client)));
        }
    }

    public function setUpClients(): void
    {
        parent::setUpClients();

        $clients = [
            $this->relayNoCache,
            $this->relay,
        ];

        if (extension_loaded('redis')) {
            $clients[] = $this->phpredis;
        }

        foreach ($clients as $client) {
            $this->setSerialization($client);
        }
    }

    public function refreshClients(): void
    {
        parent::refreshClients();

        if (extension_loaded('redis')) {
            $this->setSerialization($this->phpredis);
        }
    }

    protected function runBenchmark($client, bool $unserialize = false): int
    {
        $name = get_class($client);

        foreach ($this->keys as $key) {
            $item = $client->get("{$name}:{$key}");

            // Predis does not have built-in serialization, so if we don't
            // unserialize there, it's not really a fair comparison, since
            // both PhpRedis and Relay will pay a price for deserialization.
            if ($unserialize) {
                $item = unserialize($item); // @phpstan-ignore-line
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
        return $this->runBenchmark($this->phpredis);
    }

    public function benchmarkRelayNoCache(): int
    {
        return $this->runBenchmark($this->relayNoCache);
    }

    public function benchmarkRelay(): int
    {
        return $this->runBenchmark($this->relay);
    }

    /**
     * @param  \Redis|\Relay\Relay|\Predis\Client  $client
     * @param  array<int, object>  $items
     * @param  bool  $serialize
     * @return void
     */
    protected function seedClient($client, $items, bool $serialize = false): void
    {
        $name = get_class($client);

        foreach ($this->data as $item) {
            $client->set("{$name}:{$item->id}", $serialize ? serialize($items) : $items);
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
                array_flip(
                    array_rand($this->data, $this->chunkSize) // @phpstan-ignore-line
                )
            )
        );
    }
}
