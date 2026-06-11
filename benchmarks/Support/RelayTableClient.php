<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Relay\Table;

class RelayTableClient implements InMemoryClient
{
    public function clear(): bool
    {
        return Table::clearAll();
    }

    public function get(string $key): mixed
    {
        return Table::get($key);
    }

    public function set(string $key, mixed $value): bool
    {
        return Table::set($key, $value);
    }
}
