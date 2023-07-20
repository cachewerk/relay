<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use CacheWerk\Relay\Benchmarks\Support\BenchmarkKeyCommand;

class BenchmarkHashKeyCommand extends BenchmarkKeyCommand
{
    /**
     * @var array<int, string>
     */
    protected array $keys;

    public static function flags(): int
    {
        return self::HASH | self::READ;
    }

    public function setUp(): void
    {
        $this->flush();
        $this->setUpClients();
        $this->seed();
    }

    public function warmup(int $times, string $method): void
    {
        if ($times == 0) {
            return;
        }

        parent::warmup($times, $method);
        $this->readSimpleKeys($this->keys);
    }

    public function seed(): void
    {
        $this->keys = $this->seedSimpleKeys();
    }
}
