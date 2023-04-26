# Docker

These Docker environments are concrete examples of Relay's [installation instruction](https://relay.so/docs/installation).

## Debian (apt)

Relay has an APT package compatible with Ondřej’s wonderful `ppa:ondrej/php` repository:

```bash
docker build --pull --tag relay-debian-apt --file debian11-apt.Dockerfile .
docker run -it relay-debian-apt bash
$ php --ri relay
```

## Debian (manual)

```bash
docker build --pull --tag relay-debian --file debian10.Dockerfile .
docker run -it relay-debian bash
$ php --ri relay
```

## Debian (zts)

```bash
docker build --pull --tag relay-debian-zts --file debian10-zts.Dockerfile .
docker run -it relay-debian-zts bash
$ php --ri relay
```
