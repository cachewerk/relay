<?php

namespace CacheWerk\Relay\Benchmarks;

use Redis as PhpRedis;
use Relay\Relay as Relay;
use Predis\Client as Predis;

abstract class Benchmark
{
    protected string $host;

    protected int $port;

    protected Relay $relay;

    protected Relay $relayNC;

    protected Predis $predis;

    protected PhpRedis $phpredis;

    public function __construct(string $host, int $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    protected function redis()
    {
        return $this->setUpPredis();
    }

    protected function loadJson(string $file): array
    {
        $keys = [];
        $redis = $this->redis();
        $json = file_get_contents(__DIR__ . "/Support/data/{$file}");
        $data = json_decode($json);

        foreach ($data as $item) {
            $redis->set($item->id, serialize($item));
            $keys[] = $item->id;
        }

        return $keys;
    }

    protected function setUpRelay()
    {
        $relay = new Relay(
            $this->host,
            $this->port,
            0.5,
            0.5
        );

        $relay->setOption(Relay::OPT_MAX_RETRIES, 0);
        $relay->setOption(Relay::OPT_THROW_ON_ERROR, true);

        return $relay;
    }

    protected function setUpRelayNC()
    {
        $relay = new Relay;
        $relay->setOption(Relay::OPT_USE_CACHE, false);
        $relay->setOption(Relay::OPT_MAX_RETRIES, 0);
        $relay->setOption(Relay::OPT_THROW_ON_ERROR, true);

        $relay->connect(
            $this->host,
            $this->port,
            0.5,
            0.5
        );

        return $relay;
    }

    protected function setUpPredis()
    {
        return new Predis([
            'host' => $this->host,
            'port' => $this->port,
        ], [
            'exceptions' => true,
        ]);
    }

    protected function setUpPhpRedis()
    {
        $phpredis = new PhpRedis;

        $phpredis->connect(
            $this->host,
            $this->port,
            0.5,
            '',
            0,
            0.5
        );

        $phpredis->setOption(PhpRedis::OPT_MAX_RETRIES, 0);

        return $phpredis;
    }
}
