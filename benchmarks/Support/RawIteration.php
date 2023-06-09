<?php

namespace CacheWerk\Relay\Benchmarks\Support;

class RawIteration extends Iteration {
    public int $operations;
    public float $ms;
    public int $memory;
    public int $bytesIn;
    public int $bytesOut;

    public function __construct($operations, $ms, $memory, $bytesIn, $bytesOut) {
        $this->operations = $operations;
        $this->ms = $ms;
        $this->memory = $memory;
        $this->bytesIn = $bytesIn;
        $this->bytesOut = $bytesOut;
    }

    public function opsPerSec() {
        return $this->operations / ($this->ms / 1000);
    }

    public function finishedMessage(string $operation, string $client): string {
        return sprintf("Executed %s %s using %s in %sms seconds (%s/sec) [memory: %s, network: %s]\n",
                       CliReporter::humanNumber($this->operations),
                       $operation, $client, $this->ms,
                       CliReporter::humanNumber($this->opsPerSec()),
                       CliReporter::humanMemory($this->memory),
                       CliReporter::humanMemory($this->network));
    }
}
