# Relay PSR-16

A PSR-16 compatible cache using Relay.

[PSR-16: Common Interface for Caching Libraries](https://www.php-fig.org/psr/psr-16/)

## Usage

```php
$cache = RelayCache(
    new Relay(host: '127.0.0.1', port: 6379)
);

$cache->get('users:count');
```
