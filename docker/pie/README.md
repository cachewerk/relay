# PIE

These Docker environments install Relay using [PIE](https://github.com/php/pie) (PHP Installer for Extensions).

## PHP 8.5 (latest Relay)

Installs the latest stable Relay release on PHP 8.5.

```bash
docker build --pull --tag relay-pie-php85 --file pie/php85.Dockerfile .
docker run -it relay-pie-php85 bash
$ php --ri relay
```

## PHP 8.2 (specific Relay version)

Installs a specific Relay version on PHP 8.2. The `RELAY` build argument accepts any [version constraint](https://github.com/php/pie/blob/1.4.x/docs/usage.md) supported by PIE.

```bash
docker build --pull --tag relay-pie-php82 --file pie/php82.Dockerfile .
docker run -it relay-pie-php82 bash
$ php --ri relay
```

To install a different version:

```bash
docker build --pull --tag relay-pie-php82 --file pie/php82.Dockerfile --build-arg RELAY=0.20.0 .
```
