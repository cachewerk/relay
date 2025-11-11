<?php

namespace CacheWerk\Relay\Benchmarks\Cases;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkInMemoryCommand;

class BenchmarkInMemoryGet extends BenchmarkInMemoryCommand
{
    public static function flags(): int
    {
        return self::STRING | self::READ | self::DEFAULT;
    }

    public function command(): string
    {
        return str_replace('inmemory', '', parent::command());
    }

    public function seed(): void
    {
        $clients = $this->clients();

        foreach ($this->loadJsonFile('meteorites.json') as $item) {
            foreach ($clients as $client) {
                $client->set((string) $item['id'], $item);
            }
            $this->keys[] = $item['id'];
        }
    }
}
