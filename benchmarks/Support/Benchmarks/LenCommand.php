<?php

namespace CacheWerk\Relay\Benchmarks\Support\Benchmarks;

abstract class LenCommand extends KeyCommand
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
