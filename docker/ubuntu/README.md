# Docker

These Docker environments are concrete examples of Relay's [installation instruction](https://relay.so/docs/installation).

## Ubuntu (apt)

Relay has an APT package compatible with Ondřej’s wonderful `ppa:ondrej/php` repository:

```bash
docker build --pull --tag relay-ubuntu-apt --file ubuntu18-apt.Dockerfile .
docker run -it relay-ubuntu-apt bash
$ php --ri relay
```

The installation using APT is identical for 16.04, 18.04, 20.04 and 22.04.

## Ubuntu (manual)

```bash
docker build --pull --tag relay-ubuntu --file ubuntu20.Dockerfile .
docker run -it relay-ubuntu bash
$ php --ri relay
```
