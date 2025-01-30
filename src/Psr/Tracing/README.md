# Relay PSR-22

A PSR-22 compatible tracing layer for Relay.

- https://github.com/php-fig/fig-standards/blob/master/proposed/tracing.md
- https://github.com/php-fig/fig-standards/blob/master/proposed/tracing-meta.md

## Usage

```php
$relay = new Relay(host: '127.0.0.1', port: 6379);

// use global trace provider
$client = RelayOpenTelemetry($client);

// use custom trace provider
$client = RelayOpenTelemetry($client, $tracerProvider);

$users = $client->get('users:count');
```
