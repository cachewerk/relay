# Relay

- [Documentation](https://relaycache.com/docs)
- [Twitter](https://twitter.com/RelayCache)
- [Discussions](https://github.com/cachewerk/relay/discussions)

## Installation

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
