<?php

namespace CacheWerk\Relay\Benchmarks\Support;

use Exception;
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

    const ALL_TYPES = self::STRING | self::LIST | self::HASH | self::SET | self::ZSET | self::STREAM | self::HYPERLOGLOG | self::UTILITY;

    const READ = 0x100;

    const WRITE = 0x200;

    const DEFAULT = 0x400;

    const ALL = self::READ | self::WRITE | self::ALL_TYPES;

    protected string $host;

    protected int $port;

    /**
     * @var string|string[]
     */
    protected $auth;

    protected Relay $relay;

    protected Relay $relayNoCache;

    protected Predis $predis;

    protected PhpRedis $phpredis;

    /**
     * @param  string  $host
     * @param  int  $port
     * @param  string|string[]  $auth
     */
    public function __construct(string $host, int $port, $auth)
    {
        $this->host = $host;
        $this->port = $port;
        $this->auth = $auth;
    }

    public function name(): string
    {
        return substr((string) strrchr(static::class, '\\'), 10);
    }

    public function command(): string
    {
        return strtolower(substr((string) strrchr(static::class, '\\'), 10));
    }

    public function setUp(): void
    {
        //
    }

    abstract public static function flags(): int;

    /**
     * @param  \Redis|\Relay\Relay|\Predis\Client  $client
     * @return int
     */
    abstract protected function runBenchmark($client): int;

    public function warmup(int $times, string $method): void
    {
        if ($times == 0) {
            return;
        }

        for ($i = 0; $i < $times; $i++) {
            $this->{$method}();
        }
    }

    protected function flush(): void
    {
        $this->createPredis()->flushall();
    }

    /**
     * Helper function to flatten a multidimensional array. No type hinting here
     * as it can operate on any arbitrary array data.
     *
     * @param  array<int|string, mixed>  $input
     * @return array<int|string, string>
     */
    protected function flattenArray(array $input, string $prefix = ''): array
    {
        $result = [];

        foreach ($input as $key => $val) {
            if (is_array($val)) {
                $result = $result + $this->flattenArray($val, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = is_scalar($val) ? (string) $val : serialize($val);
            }
        }

        return $result;
    }

    protected function loadJsonFile(string $file, bool $assoc = true) // @phpstan-ignore-line
    {
        $file = __DIR__ . "/data/{$file}";

        $data = file_get_contents($file);

        if (! is_string($data)) {
            throw new Exception("Failed to load data file `{$file}`");
        }

        return json_decode((string) $data, $assoc, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Generic function to seed keys of whatever type this benchmark operates
     * against.  Will throw an exception if the command does not operate against
     * a single key type (e.g. DEL).
     *
     * @return string[] An array of string key names.
     **/
    protected function seedSimpleKeys(): array {
        $keys = [];

        $redis = $this->createPredis();
        $items = $this->loadJsonFile('meteorites.json');

        foreach ($items as $item) {
            $key = (string)$item['id'];

            if ($this->flags() & Self::STRING) {
                $redis->set($key, serialize($item));
            } else if ($this->flags() & Self::LIST) {
                $redis->rpush($key, $this->flattenArray($item));
            } else if ($this->flags() & Self::HASH) {
                $redis->hmset($key, $this->flattenArray($item));
            } else if ($this->flags() & Self::SET) {
                $redis->sadd($key, array_keys($this->flattenArray($item)));
            } else if ($this->flags() & Self::ZSET) {
                $redis->zadd($key, [array_rand($item) => mt_rand()/mt_getrandmax()]);
            } else if ($this->flags() & Self::HYPERLOGLOG) {
                $redis->pfadd($key, [array_rand($item)]);
            } else {
                throw new Exception("Unsupported key type");
            }

            $keys[] = $item['id'];
        }

        return $keys;
    }

    public function setUpClients(): void
    {
        $this->predis = $this->createPredis();
        $this->relay = $this->createRelay();
        $this->relayNoCache = $this->createRelayNoCache();

        if (extension_loaded('redis')) {
            $this->phpredis = $this->createPhpRedis();
        }
    }

    /**
     * Refresh clients after they have already been instanced. The point
     * of this method is to refresh PhpRedis and Predis as they will fail
     * horribly if you try to use them from a forked child process.
     *
     * Relay handles this automagically.
     */
    public function refreshClients(): void
    {
        $this->predis = $this->createPredis();

        if (extension_loaded('redis')) {
            $this->phpredis = $this->createPhpRedis();
        }
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
        $exclude = null;

        if (! extension_loaded('redis')) {
            $exclude = 'PhpRedis';

            Reporter::printWarning('Skipping PhpRedis runs, extension is not loaded');
        }

        return array_filter(
            get_class_methods($this),
            function ($method) use ($exclude, $filter) {
                if (! str_starts_with($method, 'benchmark')) {
                    return false;
                }

                $method = substr($method, strlen('benchmark'));

                if ($method === $exclude) {
                    return false;
                }

                if ($filter && ! preg_match("/{$filter}/i", strtolower($method))) {
                    return false;
                }

                return true;
            }
        );
    }

    public function benchmarkPredis(): int
    {
        return $this->runBenchmark($this->predis);
    }

    public function benchmarkPhpRedis(): int
    {
        return $this->runBenchmark($this->phpredis);
    }

    public function benchmarkRelayNoCache(): int
    {
        return $this->runBenchmark($this->relayNoCache);
    }

    public function benchmarkRelay(): int
    {
        return $this->runBenchmark($this->relay);
    }
}
