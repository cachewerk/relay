<?php

namespace CacheWerk\Relay\Benchmarks\Support;

abstract class BenchmarkLenCommand extends BenchmarkKeyCommand
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function seed(): void
    {
        $this->keys = $this->seedSimpleKeys();
    }
}
