<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Redis as PhpRedis;
use Relay\Relay as Relay;
use Predis\Client as Predis;

abstract class Benchmark
{
    protected string $host;

    protected int $port;

    protected ?string $auth;

    protected Relay $relay;

    protected Relay $relayNoCache;

    protected Predis $predis;

    protected PhpRedis $phpredis;

    public function __construct(string $host, int $port, ?string $auth)
    {
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
    }

    abstract public function getName(): string;
    abstract public function seedKeys(): void;

    public function setUp(): void {
    }

    protected function flush(): void
    {
        $this->createPredis()->flushall();
    }

    /**
     * @param array<mixed> $input
     * @return array<mixed>
     *
     * Helper function to flatten a multidimensional array.  No type hinting here
     * as it can operate on any arbitrary array data.
     */
    protected function flattenArray(array $input, string $prefix = ''): array {
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

    /** @phpstan-ignore-next-line */
    protected function loadJsonFile(string $file, bool $assoc) {
        $file = __DIR__ . "/data/{$file}";

        $data = file_get_contents($file);
        if ( ! is_string($data))
            throw new \Exception("Failed to load data file '$file'");

        return json_decode((string)$data, $assoc, 512, JSON_THROW_ON_ERROR);
    }

    public function setUpClients(): void {
        $this->predis = $this->createPredis();
        $this->phpredis = $this->createPhpRedis();
        $this->relay = $this->createRelay();
        $this->relayNoCache = $this->createRelayNoCache();
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

    /**
     * @return Relay
     */
    protected function createRelayNoCache()
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

    /**
     * @return PhpRedis
     */
    protected function createPhpRedis()
    {
        $phpredis = new PhpRedis;
        $phpredis->connect($this->host, $this->port, 0.5, '', 0, 0.5);
        $phpredis->setOption(PhpRedis::OPT_MAX_RETRIES, 0);

        if ($this->auth) {
            $phpredis->auth($this->auth);
        }

        return $phpredis;
    }

    /**
     * @return Predis
     */
    protected function createPredis()
    {
        $parameters = [
            'host' => $this->host,
            'port' => $this->port,
            'password' => $this->auth,
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
     * @return Array<string>
     */
    public function getBenchmarkMethods(string $filter): array {
        return array_filter(
            get_class_methods($this),
            function ($method) use ($filter) {
                if (!str_starts_with($method, 'benchmark'))
                    return false;
                $method = substr($method, strlen('benchmark'));
                return !$filter || preg_match("/$filter/i", strtolower($method));
            }
        );
    }
}
