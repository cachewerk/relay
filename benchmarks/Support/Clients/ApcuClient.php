<?php

namespace CacheWerk\Relay\Benchmarks\Support\Clients;

class ApcuClient implements InMemoryClient
{
    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    public function get(string $key): mixed
    {
        return apcu_fetch($key);
    }

    public function set(string $key, mixed $value): bool
    {
        return (bool) apcu_store($key, $value);
    }
}
