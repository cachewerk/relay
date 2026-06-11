# Relay Benchmarks

## Requirements

- PHP 7.4+
- Redis 6.0+

## Installation

First, follow the [installation instructions](https://relay.so/docs/installation) to set up the Relay extension for PHP.

Next, clone the Relay repository and install the Composer dependencies:

```bash
git clone https://github.com/cachewerk/relay.git
cd relay
composer install
```

## Running benchmarks

To run a set of quick, default benchmarks, execute:

```bash
composer run bench

# List all available options
composer run bench -- --help

# Compare local-only caches (APCu and Relay\Table)
php -d apc.enable_cli=1 benchmarks/run --command-type memory
```

> [!NOTE]
> Caveat 1: The results on Silicon are misleading, run the benchmarks on actual production infrastructure.   
> Caveat 2: New Relic, Blacfire, Xdebug and similar profilers will significantly slow down Relay and skew the benchmarks.

### Connection and authentication

```bash
# Non-default Redis host and port
./run -h 127.0.0.3 -p 7000

# Use unix socket
./run -h /var/redis.sock

# Specify a password
./run -a p4ssw0rd

# Specify ACL username and password
./run --user picard --pass p4ssw0rd
```

### Examples

```bash
# Run one specific, multiple or all benchmarks
./run --all
./run BenchmarkHMGET.php
./run BenchmarkHMGET.php BenchmarkHGETALL.php 

# Specify the number of worker threads
./run --workers=24

# Specify the number of runs and duration in seconds for each run
./run --runs=5 --duration=10

# Run only "hash read" benchmarks
./run --key-type=hash --command-type=read

# Run benchmarks with only Relay and Predis
./run --filter relay,predis

# Output JSON
./run --json

# Output verbose information
./run -v [--verbose]
```
