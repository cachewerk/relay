<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\Benchmarks\InMemoryCommand;

class BenchmarkTable extends InMemoryCommand
{
    public static function flags(): int
    {
        return self::STRING | self::MEMORY;
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
