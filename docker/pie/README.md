# PIE

These Docker environments install Relay using [PIE](https://github.com/php/pie) (PHP Installer for Extensions).

## Latest stable version

Installs the latest stable Relay release.

```bash
docker build --pull --tag relay-pie-latest --file pie/pie-latest.Dockerfile .
docker run -it relay-pie-latest bash
$ php --ri relay
```

## Pinned version

Installs a specific Relay version. The `RELAY` build argument accepts any [version constraint](https://github.com/php/pie/blob/1.4.x/docs/usage.md) supported by PIE.

```bash
docker build --pull --tag relay-pie-pinned --file pie/pie-pinned.Dockerfile .
docker run -it relay-pie-pinned bash
$ php --ri relay
```
