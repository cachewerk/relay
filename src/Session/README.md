# Relay session handler

```php
$relay = new Relay\Relay(host: '127.0.0.1', port: 6379);

$handler = new CacheWerk\Relay\Session\RelaySessionHandler($relay);
$handler->register();
