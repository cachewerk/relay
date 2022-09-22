# Relay

- [Documentation](https://relay.so/docs)
- [Twitter](https://twitter.com/RelayPHP)
- [Discord](https://discord.gg/exYBXqTXgY)
- [Discussions](https://github.com/cachewerk/relay/discussions)

## Installation

First, follow the [installation instructions](https://relay.so/docs/installation) to set up the Relay extension for PHP.

Next, either try the [Laravel](https://github.com/cachewerk/relay/tree/main/src/Laravel), [WordPress](https://objectcache.pro/docs/relay/) or [Magento](https://github.com/cachewerk/magento-relay) integrations.

Alternatively, grab the Composer package and run the benchmarks:

```bash
composer require cachewerk/relay
```

## Benchmarks

The host and port may be set in `phpbench.json`.

```bash
composer run bench
# ./vendor/bin/phpbench run --report=redis

composer run bench:verbose
# ./vendor/bin/phpbench run --report=redis --progress=blinken
```

> Note: While we're huge fans of New Relic, having the APM agent enabled will significantly slow down Relay and skew the benchmarks.
