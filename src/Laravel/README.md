# Relay for Laravel

Relay can be used as a drop-in replacement for PhpRedis with Laravel.

## Installation

Be sure to follow the [installation instructions](https://relaycache.com/docs/installation) for the Relay extension itself.

```bash
composer require cachewerk/relay
```

The Service Provider and Facade are auto-discovered.

Simply set your `REDIS_CLIENT` to `relay`, or update your the `redis.client` in your `config/database.php` configuration file.

## Usage

Laravel will use Relay for all its Redis connections, depending on your configuration.

Use the cache, sessions, queues and broadcasting modules as usual.

You may also use the Relay facade directly:

```php
Relay::stats();
```
