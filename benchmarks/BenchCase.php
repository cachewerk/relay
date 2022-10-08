<?php

namespace CacheWerk\Relay\Benchmarks;

use Relay\Relay;
use Redis as PhpRedis;
use Credis_Client as Credis;
use Predis\Client as Predis;

abstract class BenchCase
{
    /**
     * The Credis client.
     *
     * @var \Credis_Client
     */
    protected $credis;

    /**
     * The Predis client.
     *
     * @var \Predis\Client
     */
    protected $predis;

    /**
     * The PhpRedis client.
     *
     * @var \Redis
     */
    protected $phpredis;

    /**
     * The Relay client (without in-memory caching).
     *
     * @var \Relay\Relay
     */
    protected $relay;

    /**
     * The Relay client (with in-memory caching).
     *
     * @var \Relay\Relay
     */
    protected $relayCache;

    /**
     * Returns a PhpRedis connection used for seeding data and flushing.
     *
     * @return \Redis
     */
    public static function redis()
    {
        $redis = new PhpRedis;
        $redis->connect($_SERVER['REDIS_HOST'], $_SERVER['REDIS_PORT']);
        $redis->setOption(PhpRedis::OPT_SERIALIZER, PhpRedis::SERIALIZER_PHP);
        $redis->ping();

        return $redis;
    }

    /**
     * Establishes, stores and returns a Credis connection.
     *
     * @return \Credis_Client
     */
    public function setUpCredis(): Credis
    {
        $this->credis = new Credis($_SERVER['REDIS_HOST'], $_SERVER['REDIS_PORT']);
        $this->credis->forceStandalone();
        $this->credis->ping();

        return $this->credis;
    }

    /**
     * Establishes, stores and returns a Predis connection.
     *
     * @return \Predis\Client
     */
    public function setUpPredis(): Predis
    {
        $this->predis = new Predis([
            'host' => $_SERVER['REDIS_HOST'],
            'port' => $_SERVER['REDIS_PORT'],
        ]);

        $this->predis->ping();

        return $this->predis;
    }

    /**
     * Establishes, stores and returns a PhpRedis connection.
     *
     * @return \Redis
     */
    public function setUpPhpRedis(): PhpRedis
    {
        $this->phpredis = new PhpRedis;
        $this->phpredis->connect($_SERVER['REDIS_HOST'], $_SERVER['REDIS_PORT']);
        $this->phpredis->ping();

        return $this->phpredis;
    }

    /**
     * Establishes, stores and returns a Relay connection (with in-memory caching disabled).
     *
     * @return \Relay\Relay
     */
    public function setUpRelay(): Relay
    {
        $this->relay = new Relay($_SERVER['REDIS_HOST'], $_SERVER['REDIS_PORT'], 0, 0, ['use-cache' => false]);
        $this->relay->ping();

        return $this->relay;
    }

    /**
     * Establishes, stores and returns a Relay connection (with in-memory caching enabled).
     *
     * @return \Relay\Relay
     */
    public function setUpRelayCache(): Relay
    {
        $this->relayCache = new Relay;
        $this->relayCache->connect($_SERVER['REDIS_HOST'], $_SERVER['REDIS_PORT']);
        $this->relayCache->ping();

        return $this->relayCache;
    }

    /**
     * Loads and returns decoded JSON data.
     *
     * @param  string  $filename
     * @return array
     */
    public static function loadJson($filename) // @phpstan-ignore-line
    {
        return (array) json_decode(
            (string) file_get_contents(__DIR__ . "/data/{$filename}"),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
