<?php

declare(strict_types=1);

namespace CacheWerk\Relay\Psr\SimpleCache;

use DateTime;
use TypeError;
use Throwable;
use Traversable;
use DateInterval;

use Relay\Relay;

use Psr\SimpleCache\CacheInterface;

class RelayCache implements CacheInterface
{
    protected Relay $relay;

    /**
     * Creates a new `RelayCache` instance.
     *
     * @param  \Relay\Relay $relay
     * @return void
     */
    public function __construct(Relay $relay)
    {
        $this->relay = new $relay;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null): mixed
    {
        try {
            $item = $this->relay->get($key);
        } catch (TypeError $error) {
            throw new InvalidArgumentException($error->getMessage(), $error->getCode(), $error);
        } catch (Throwable $th) {
            throw new CacheException($th->getMessage(), $th->getCode(), $th);
        }

        return $item === false ? $default : $item;
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null): bool
    {
        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTime)->add($ttl)->getTimeStamp() - time();
        }

        try {
            return is_null($ttl)
                ? $this->relay->set($key, $value)
                : $this->relay->setex($key, $ttl, $value);
        } catch (TypeError $error) {
            throw new InvalidArgumentException($error->getMessage(), $error->getCode(), $error);
        } catch (Throwable $th) {
            throw new CacheException($th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key): bool
    {
        try {
            return (bool) $this->relay->del($key);
        } catch (TypeError $error) {
            throw new InvalidArgumentException($error->getMessage(), $error->getCode(), $error);
        } catch (Throwable $th) {
            throw new CacheException($th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        try {
            return $this->relay->flushdb();
        } catch (Throwable $th) {
            throw new CacheException($th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null): iterable
    {
        if ($keys instanceof Traversable) {
            $keys = \iterator_to_array($keys, false);
        }

        if (! \is_array($keys)) {
            throw new InvalidArgumentException(
                \sprintf('Cache keys must be array or Traversable, "%s" given.', \gettype($keys))
            );
        }

        try {
            $items = $this->relay->mget($keys);
        } catch (TypeError $error) {
            throw new InvalidArgumentException($error->getMessage(), $error->getCode(), $error);
        } catch (Throwable $th) {
            throw new CacheException($th->getMessage(), $th->getCode(), $th);
        }

        return array_map(function ($value) use ($default) {
            return $value === false ? $default : $value;
        }, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null): bool
    {
        if ($values instanceof Traversable) {
            $values = \iterator_to_array($values, false);
        }

        if (! \is_array($values)) {
            throw new InvalidArgumentException(
                \sprintf('Cache keys must be array or Traversable, "%s" given.', \gettype($values))
            );
        }

        if ($ttl instanceof DateInterval) {
            $ttl = (new DateTime)->add($ttl)->getTimeStamp() - time();
        }

        try {
            if (is_null($ttl)) {
                return $this->relay->mset($values);
            }

            foreach ($values as $key => $value) {
                $this->relay->setex($key, $ttl, $value);
            }

            return true;
        } catch (TypeError $error) {
            throw new InvalidArgumentException($error->getMessage(), $error->getCode(), $error);
        } catch (Throwable $th) {
            throw new CacheException($th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys): bool
    {
        if ($keys instanceof Traversable) {
            $keys = \iterator_to_array($keys, false);
        }

        if (! \is_array($keys)) {
            throw new InvalidArgumentException(
                \sprintf('Cache keys must be array or Traversable, "%s" given.', \gettype($keys))
            );
        }

        try {
            return (bool) $this->relay->del(...$keys);
        } catch (TypeError $error) {
            throw new InvalidArgumentException($error->getMessage(), $error->getCode(), $error);
        } catch (Throwable $th) {
            throw new CacheException($th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        try {
            return $this->relay->exists($key);
        } catch (TypeError $error) {
            throw new InvalidArgumentException($error->getMessage(), $error->getCode(), $error);
        } catch (Throwable $th) {
            throw new CacheException($th->getMessage(), $th->getCode(), $th);
        }
    }
}
