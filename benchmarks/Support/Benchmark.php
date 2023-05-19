<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Redis as PhpRedis;
use Relay\Relay as Relay;
use Predis\Client as Predis;

abstract class Benchmark
{
    protected string $host;

    protected int $port;

    protected Relay $relay;

    protected Relay $relayNoCache;

    protected Predis $predis;

    protected PhpRedis $phpredis;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function its()
    {
        return static::Iterations;
    }

    public function revs()
    {
        return static::Revolutions;
    }

    public function opsTotal()
    {
        return static::Operations * static::Revolutions;
    }

    protected function flush()
    {
        return $this->createPredis()->flushall();
    }

    protected function loadJson(string $file): array
    {
        $keys = [];
        $json = file_get_contents(__DIR__ . "/data/{$file}");
        $data = json_decode($json);

        $redis = $this->createPredis();

        foreach ($data as $item) {
            $redis->set($item->id, serialize($item));
            $keys[] = $item->id;
        }

        return $keys;
    }

    protected function setUpClients()
    {
        $this->predis = $this->createPredis();
        $this->phpredis = $this->createPhpRedis();
        $this->relay = $this->createRelay();
        $this->relayNoCache = $this->createRelayNoCache();
    }

    protected function createRelay()
    {
        $relay = new Relay;
        $relay->setOption(Relay::OPT_MAX_RETRIES, 0);
        $relay->setOption(Relay::OPT_THROW_ON_ERROR, true);

        $relay->connect($this->host, $this->port, 0.5, 0.5);
        $relay->flushMemory();

        return $relay;
    }

    protected function createRelayNoCache()
    {
        $relay = new Relay;
        $relay->setOption(Relay::OPT_USE_CACHE, false);
        $relay->setOption(Relay::OPT_MAX_RETRIES, 0);
        $relay->setOption(Relay::OPT_THROW_ON_ERROR, true);

        $relay->connect($this->host, $this->port, 0.5, 0.5);
        $relay->flushMemory();

        return $relay;
    }

    protected function createPhpRedis()
    {
        $phpredis = new PhpRedis;
        $phpredis->connect($this->host, $this->port, 0.5, '', 0, 0.5);
        $phpredis->setOption(PhpRedis::OPT_MAX_RETRIES, 0);

        return $phpredis;
    }

    protected function createPredis()
    {
        return new Predis([
            'host' => $this->host,
            'port' => $this->port,
            'timeout' => 0.5,
            'read_write_timeout' => 0.5,
        ], [
            'exceptions' => true,
        ]);
    }
}
