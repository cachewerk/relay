# Relay session handler

```php
$relay = new Relay\Relay;
$relay->connect('127.0.0.1', 6379);

$handler = new CacheWerk\Relay\Session\RelaySessionHandler($relay);
$handler->register();
```

## Customizations

```php
$relay = new Relay\Relay;

$relay->connect(
    host: '127.0.0.1',
    port: 6379,

    // use a dedicated Redis database for session data
    database: 15,
);

// prefix session keys
$relay->setOption($relay::OPT_PREFIX, 'session:');

// compress session data
$relay->setOption($relay::OPT_COMPRESSION, $relay::COMPRESSION_LZF);
$relay->setOption($relay::OPT_SERIALIZER, $relay::SERIALIZER_IGBINARY);

// create session handler
$handler = new CacheWerk\Relay\Session\RelaySessionHandler($relay);

// tell PHP to use the session handler
$handler->register();
```
