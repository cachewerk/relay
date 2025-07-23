<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Credis;

use Relay\Relay;
use Relay\Exception;

use LogicException;

class RelayCredisAdapter
{
    /**
     * The Relay client.
     *
     * @var \Relay\Relay
     */
    protected $relay;

    /**
     * Whether we're in a transaction.
     *
     * @see Credis_Client::__call()
     *
     * @var bool
     */
    protected $isMulti = false;

    /**
     * The transaction instance.
     *
     * @see Credis_Client::__call()
     *
     * @var \Relay\Relay|null
     */
    protected $redisMulti = null;

    /**
     * Create a new instance.
     *
     * @param  \Relay\Relay  $relay
     * @return void
     */
    public function __construct(Relay $relay)
    {
        $this->relay = $relay;
    }

    /**
     * Hijack method.
     *
     * @throws \LogicException
     */
    public function connect(): void
    {
        throw new LogicException('Connection must be established in Relay');
    }

    /**
     * Handle Credis' odd pipeline/multi syntax.
     *
     * @param  string  $name
     * @param  array<int, array<string>>  $args
     * @return mixed
     */
    public function __call($name, $args)
    {
        $name = strtolower($name);

        if (in_array($name, ['subscribe', 'psubscribe', 'sscan', 'hscan', 'zscan'])) {
            throw new \Exception("Command `{$name}` not implemented.");
        }

        $args = $this->_transformArguments($name, $args);

        try {
            if ($name == 'pipeline' || $name == 'multi') {
                if ($this->isMulti) {
                    return $this;
                } else {
                    $this->isMulti = true;
                    $this->redisMulti = $this->relay->{$name}() ?: null; // @phpstan-ignore-line

                    return $this;
                }
            } elseif ($name == 'exec' || $name == 'discard') {
                $this->isMulti = false;
                $response = $this->redisMulti ? $this->redisMulti->{$name}() : null;
                $this->redisMulti = null;

                return $response;
            }

            if ($this->isMulti) {
                $this->redisMulti->{$name}(...$args);

                return $this;
            }

            try {
                $response = $this->relay->{$name}(...$args);
            } catch (Exception $exception) {
                throw $exception;
            }
        } catch (Exception $exception) {
            throw $exception;
        }

        return $response;
    }

    /**
     * Transform arguments. Mimics what Credis does.
     *
     * @see Credis_Client::__call()
     *
     * @param  string  $command
     * @param  array<int, array<string>>  $args
     * @return array<mixed>
     */
    protected function _transformArguments(string $command, $args)
    {
        switch ($command) {
            case 'get':
            case 'set':
            case 'hget':
            case 'hset':
            case 'setex':
            case 'mset':
            case 'msetnx':
            case 'hmset':
            case 'hmget':
            case 'del':
            case 'zrangebyscore':
            case 'zrevrangebyscore':
                break;
            case 'zrange':
            case 'zrevrange':
                if (isset($args[3]) && is_array($args[3])) {
                    $cArgs = $args[3];
                    $args[3] = ! empty($cArgs['withscores']);
                }

                $args = $this->_flattenArguments($args);

                break;
            case 'zinterstore':
            case 'zunionstore':
                $cArgs = [];
                $cArgs[] = array_shift($args);
                $cArgs[] = array_shift($args);

                if (isset($args[0]) && isset($args[0]['weights'])) {
                    $cArgs[] = (array) $args[0]['weights'];
                } else {
                    $cArgs[] = null;
                }

                if (isset($args[0]) && isset($args[0]['aggregate'])) {
                    $cArgs[] = strtoupper($args[0]['aggregate']);
                }

                $args = $cArgs;

                break;
            case 'mget':
                if (isset($args[0]) && ! is_array($args[0])) {
                    $args = [$args];
                }

                break;
            case 'lrem':
                $args = [$args[0], $args[2], $args[1]];

                break;
            case 'eval':
            case 'evalsha':
                if (isset($args[1]) && is_array($args[1])) {
                    $cKeys = $args[1];
                } elseif (isset($args[1]) && is_string($args[1])) {
                    $cKeys = [$args[1]];
                } else {
                    $cKeys = [];
                }

                if (isset($args[2]) && is_array($args[2])) {
                    $cArgs = $args[2];
                } elseif (isset($args[2]) && is_string($args[2])) {
                    $cArgs = [$args[2]];
                } else {
                    $cArgs = [];
                }

                $args = [$args[0], array_merge($cKeys, $cArgs), count($cKeys)];

                break;
            case 'subscribe':
            case 'psubscribe':
                break;
            case 'scan':
            case 'sscan':
            case 'hscan':
            case 'zscan':
                break;
            default:
                $args = $this->_flattenArguments($args);
        }

        return $args;
    }

    /**
     * Flatten arguments. Mimics what Credis does.
     *
     * @see Credis_Client::_flattenArguments()
     *
     * @param  array<mixed>  $arguments
     * @param  array<mixed>  &$out
     * @return array<mixed>
     */
    protected function _flattenArguments(array $arguments, &$out = [])
    {
        foreach ($arguments as $key => $arg) {
            if (! is_int($key)) {
                $out[] = $key;
            }

            if (is_array($arg)) {
                self::_flattenArguments($arg, $out);
            } else {
                $out[] = $arg;
            }
        }

        return $out;
    }
}
