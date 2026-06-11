<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkInMemoryCommand;

class BenchmarkInMemoryGet extends BenchmarkInMemoryCommand
{
    public static function flags(): int
    {
        return self::STRING | self::MEMORY | self::DEFAULT;
    }

    public function command(): string
    {
        return 'get';
    }

    public function seed(): void
    {
        $clients = $this->clients();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            $key = (string) $item['id'];

            foreach ($clients as $client) {
                $client->set($key, $item);
            }

            $this->keys[] = $key;
        }
    }
}
