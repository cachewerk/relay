<?php

namespace CacheWerk\Relay\Benchmarks\Support\Benchmarks;

use Relay\Table;

use CacheWerk\Relay\Benchmarks\Support\Reporter;
use CacheWerk\Relay\Benchmarks\Support\Clients\ApcuClient;
use CacheWerk\Relay\Benchmarks\Support\Clients\InMemoryClient;
use CacheWerk\Relay\Benchmarks\Support\Clients\RelayTableClient;

abstract class InMemoryCommand extends KeyCommand
{
    protected RelayTableClient $table;

    protected ApcuClient $apcu;

    public function setUp(): void
    {
        $this->setUpClients();
        $this->flush();

        if (method_exists($this, 'seed')) {
            $this->seed();
        }
    }

    public function setUpClients(): void
    {
        parent::setUpClients();

        if (class_exists(Table::class)) {
            $this->table = new RelayTableClient;
        }

        if (extension_loaded('apcu')) {
            $this->apcu = new ApcuClient;
        }
    }

    protected function subjects(): array
    {
        $subjects = [];

        if (class_exists(Table::class)) {
            $subjects[] = 'RelayTable';
        } else {
            Reporter::printWarning('Skipping Relay\Table runs, class is not available');
        }

        if (extension_loaded('apcu')) {
            $subjects[] = 'APCu';
        } else {
            Reporter::printWarning('Skipping APCu runs, extension is not loaded');
        }

        return $subjects;
    }

    /**
     * @return array<int, InMemoryClient>
     */
    protected function clients(): array
    {
        $clients = [];

        if (isset($this->table)) {
            $clients[] = $this->table;
        }

        if (isset($this->apcu)) {
            $clients[] = $this->apcu;
        }

        return $clients;
    }

    protected function flush(): void
    {
        foreach ($this->clients() as $client) {
            $client->clear();
        }
    }

    public function benchmarkRelayTable(): int
    {
        return $this->runBenchmark($this->table);
    }

    public function benchmarkAPCu(): int
    {
        return $this->runBenchmark($this->apcu);
    }
}
