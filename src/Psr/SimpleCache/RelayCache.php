<?php

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
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \Psr\SimpleCache\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get($key, $default = null)
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
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store. Must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set($key, $value, $ttl = null)
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
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete($key)
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
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\CacheException
     */
    public function clear()
    {
        try {
            return $this->relay->flushdb();
        } catch (Throwable $th) {
            throw new CacheException($th->getMessage(), $th->getCode(), $th);
        }
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMultiple($keys, $default = null)
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
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null)
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
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function deleteMultiple($keys)
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
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it, making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\CacheException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function has($key)
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
