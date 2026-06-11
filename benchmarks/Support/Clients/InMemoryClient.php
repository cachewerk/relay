<?php

namespace CacheWerk\Relay\Benchmarks\Support\Clients;

interface InMemoryClient
{
    public function clear(): bool;

    public function get(string $key): mixed;

    public function set(string $key, mixed $value): bool;
}
