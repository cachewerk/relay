<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class CpuInfo
{
    public string $type;

    public int $cores;

    public int $threads;

    public string $arch;

    public function __construct(string $type, int $cores, int $threads, string $arch)
    {
        $this->type = $type;
        $this->cores = $cores;
        $this->threads = $threads;
        $this->arch = $arch;
    }
}
