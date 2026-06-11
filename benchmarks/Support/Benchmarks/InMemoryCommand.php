<?php

namespace CacheWerk\Relay\Benchmarks\Support\Benchmarks;

use Exception;

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
            $this->verifySeed();
        }
    }

    /**
     * APCu silently drops writes when it's disabled at runtime, and a future
     * case might seed only one of the stores — either way every read becomes
     * a near-instant miss and the results look spectacular while measuring
     * nothing. Fail loudly instead.
     */
    protected function verifySeed(): void
    {
        $key = (string) $this->keys[0];

        foreach ($this->clients() as $client) {
            if (in_array($client->get($key), [null, false], true)) {
                throw new Exception(sprintf(
                    'Seeding %s had no effect, key `%s` reads back empty',
                    $client::class,
                    $key
                ));
            }
        }
    }

    public function setUpClients(): void
    {
        parent::setUpClients();

        if (class_exists(Table::class)) {
            $this->table = new RelayTableClient;
        }

        if (extension_loaded('apcu') && apcu_enabled()) {
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

        if (! extension_loaded('apcu')) {
            Reporter::printWarning('Skipping APCu runs, extension is not loaded');
        } elseif (! apcu_enabled()) {
            Reporter::printWarning('Skipping APCu runs, extension is disabled (set apc.enable_cli=1)');
        } else {
            $subjects[] = 'APCu';
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
