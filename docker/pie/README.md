# PIE

These Docker environments install Relay using [PIE](https://github.com/php/pie) (PHP Installer for Extensions).

## Latest Relay

Installs the latest stable Relay release.

```bash
docker build --pull --tag relay-pie-latest --file pie/latest.Dockerfile .
docker run -it relay-pie-latest bash
$ php --ri relay
```

## Pinned Relay version

Installs a specific Relay version. The `RELAY` build argument accepts any [version constraint](https://github.com/php/pie/blob/1.4.x/docs/usage.md) supported by PIE.

```bash
docker build --pull --tag relay-pie-pinned --file pie/pinned.Dockerfile .
docker run -it relay-pie-pinned bash
$ php --ri relay
```

To install a different version:

```bash
docker build --pull --tag relay-pie-pinned --file pie/pinned.Dockerfile --build-arg RELAY=^0.20.0 .
```
