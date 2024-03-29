# Relay for Laravel

Relay can be used as a drop-in replacement for PhpRedis with Laravel.

## Installation

Be sure to follow the [installation instructions](https://relay.so/docs/installation) for the Relay extension itself.

```bash
composer require cachewerk/relay
```

The Relay Service Provider and Facade are auto-discovered.

To use Relay for all Redis connections, set your `REDIS_CLIENT` in your `.env` file:

```
REDIS_CLIENT=relay
```

Or set the `redis.client` option in the `config/database.php` configuration file to `relay`.

## Usage

You can use Laravel's cache, sessions and queues as usual. For Relay-specific methods, you can use it's facade:

```php
use CacheWerk\Relay\Laravel\Relay;

// dump statistics
Relay::stats();

// register Relay event callback
Relay::onFlushed(fn ($event) => dd($event));

// register Relay event callback
Relay::onInvalidated(fn ($event) => dd($event->key));
```
