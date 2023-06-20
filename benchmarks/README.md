# Relay Benchmarks

## Requirements

- PHP 7.4+
- Redis 6.0+

## Installation

First, follow the [installation instructions](https://relay.so/docs/installation) to set up the Relay extension for PHP.

Next, clone the Relay repository and install the Composer dependencies:

```bash
git clone git@github.com:cachewerk/relay.git
cd relay
composer install
```

If Composer isn't available on the system, download it:

```bash
wget https://getcomposer.org/download/latest-stable/composer.phar
./composer.phar install
```

## Benchmarks

To run all benchmarks, execute:

```bash
composer run bench
```

> Caveat 1: The results on Silicon are misleading, run the benchmarks on actual production infrastructure.   
> Caveat 2: New Relic, Blacfire, Xdebug and similar profilers will significantly slow down Relay and skew the benchmarks.

To run specific benchmarks, use:

```bash
# Run specific benchmarks
./run BenchmarkMGET.php BenchmarkGET.php

# Pass Redis host and port
./run -h 127.0.0.1 -p 7000

# Use unix socket
./run -s /tmp/redis.sock

# Pass Redis password
./run -a p4ssw0rd

# Output verbose information
./run -v [--verbose]
```
