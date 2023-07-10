<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Redis as PhpRedis;
use Relay\Relay as Relay;
use Predis\Client as Predis;

abstract class Benchmark
{
    const STRING = 0x01;

    const LIST = 0x02;

    const HASH = 0x04;

    const SET = 0x08;

    const ZSET = 0x10;

    const STREAM = 0x20;

    const HYPERLOGLOG = 0x40;

    const UTILITY = 0x80;

    const ALL = self::STRING | self::LIST | self::HASH | self::SET | self::ZSET | self::STREAM | self::HYPERLOGLOG | self::UTILITY;

    const READ = 0x100;

    const WRITE = 0x200;

    protected string $host;

    protected int $port;

    protected mixed $auth;

    protected Relay $relay;

    protected Relay $relayNoCache;

    protected Predis $predis;

    protected PhpRedis $phpredis;

    public function __construct(string $host, int $port, mixed $auth)
    {
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
    }

    abstract public function getName(): string;

    abstract public function seedKeys(): void;

    abstract public static function flags(): int;

    public function warmup(int $times, string $method): void
    {
        if ($times == 0) {
            return;
        }

        for ($i = 0; $i < $times; $i++) {
            $this->{$method}();
        }
    }

    public function setUp(): void
    {
        //
    }

    protected function flush(): void
    {
        $this->createPredis()->flushall();
    }

    /**
     * Helper function to flatten a multidimensional array.  No type hinting here
     * as it can operate on any arbitrary array data.
     */
    protected function flattenArray(array $input, string $prefix = ''): array
    {
        $result = [];

        foreach ($input as $key => $val) {
            if (is_array($val)) {
                $result = $result + $this->flattenArray($val, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $val;
            }
        }

        return $result;
    }

    protected function loadJsonFile(string $file, bool $assoc)
    {
        $file = __DIR__ . "/data/{$file}";

        $data = file_get_contents($file);
        if (! is_string($data)) {
            throw new \Exception("Failed to load data file '$file'");
        }

        return json_decode((string) $data, $assoc, 512, JSON_THROW_ON_ERROR);
    }

    public function setUpClients(): void
    {
        $this->predis = $this->createPredis();
        $this->phpredis = $this->createPhpRedis();
        $this->relay = $this->createRelay();
        $this->relayNoCache = $this->createRelayNoCache();
    }

    /**
     * Refresh clients after they have already been instanced.  The point
     * of this method is to refresh PhpRedis and Predis as they will fail
     * horribly if you try to use them from a forked child process.
     *
     * Relay handles this automagically.
     */
    public function refreshClients(): void
    {
        $this->predis = $this->createPredis();
        $this->phpredis = $this->createPhpRedis();
    }

    /**
     * @return Relay
     */
    protected function createRelay()
    {
        $relay = new Relay;
        $relay->setOption(Relay::OPT_MAX_RETRIES, 0);
        $relay->setOption(Relay::OPT_THROW_ON_ERROR, true);

        $relay->connect($this->host, $this->port, 0.5, '', 0, 0.5);

        if ($this->auth) {
            $relay->auth($this->auth);
        }

        $relay->flushMemory();

        return $relay;
    }

    protected function createRelayNoCache(): Relay
    {
        $relay = new Relay;
        $relay->setOption(Relay::OPT_USE_CACHE, false);
        $relay->setOption(Relay::OPT_MAX_RETRIES, 0);
        $relay->setOption(Relay::OPT_THROW_ON_ERROR, true);

        $relay->connect($this->host, $this->port, 0.5, '', 0, 0.5);

        if ($this->auth) {
            $relay->auth($this->auth);
        }

        $relay->flushMemory();

        return $relay;
    }

    protected function createPhpRedis(): PhpRedis
    {
        $phpredis = new PhpRedis;
        $phpredis->connect($this->host, $this->port, 0.5, '', 0, 0.5);
        $phpredis->setOption(PhpRedis::OPT_MAX_RETRIES, 0);

        if ($this->auth) {
            /** @phpstan-ignore-next-line */
            $phpredis->auth($this->auth);
        }

        return $phpredis;
    }

    protected function createPredis(): Predis
    {
        if (is_array($this->auth) && count($this->auth) == 2) {
            [$user, $pass] = $this->auth;
        } else {
            $user = null;
            $pass = $this->auth;
        }

        $parameters = [
            'host' => $this->host,
            'port' => $this->port,
            'username' => $user,
            'password' => $pass,
            'timeout' => 0.5,
            'read_write_timeout' => 0.5,
        ];

        if (! $this->port) {
            $parameters['scheme'] = 'unix';
            $parameters['path'] = $this->host;
        }

        return new Predis($parameters, [
            'exceptions' => true,
        ]);
    }

    /**
     * @return array<string>
     */
    public function getBenchmarkMethods(string $filter): array
    {
        return array_filter(
            get_class_methods($this),
            function ($method) use ($filter) {
                if (! str_starts_with($method, 'benchmark')) {
                    return false;
                }

                $method = substr($method, strlen('benchmark'));

                return ! $filter || preg_match("/$filter/i", strtolower($method));
            }
        );
    }
}
