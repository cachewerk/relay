# Relay

[Documentation](https://relay.so/docs) |
[Twitter](https://twitter.com/RelayPHP) |
[Discord](https://discord.gg/exYBXqTXgY) |
[Discussions](https://github.com/cachewerk/relay/discussions) |
[API](https://relay.so/api)

## Installation

First, follow the [installation instructions](https://relay.so/docs/installation) to set up the Relay extension for PHP.

Next, try the [Laravel](https://github.com/cachewerk/relay/tree/main/src/Laravel), [Symfony](https://symfony.com/doc/current/components/cache/adapters/redis_adapter.html), [WordPress](https://objectcache.pro/docs/relay/), [Drupal](https://www.drupal.org/project/redis) or [Magento](https://github.com/cachewerk/magento-relay) integrations.

Alternatively, grab the Composer package and run the benchmarks:

```bash
composer require cachewerk/relay
```

## Benchmarks

```bash
git clone git@github.com:cachewerk/relay.git
cd relay
composer install

composer run bench -- --redis=tcp://127.0.0.1:6379
```

> Caveat 1: The results on Silicon are misleading, run the benchmark on your actual x86 infrastructure.   
> Caveat 2: New Relic and similar profilers will significantly slow down Relay and skew the benchmarks.
