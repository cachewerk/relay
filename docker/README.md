# Docker

These Docker environments are concrete examples of Relay's [installation instruction](https://relaycache.com/docs/installation).

## Ubuntu

```bash
docker build --pull --tag relay-ubuntu-focal --file ubuntu20.Dockerfile .
docker run -it relay-ubuntu-focal bash
$ php --ri relay
```

Relay also has an APT package compatible with Ondřej’s wonderful `ppa:ondrej/php` repository:

```bash
docker build --pull --tag relay-ubuntu-bionic-apt --file ubuntu18-apt.Dockerfile .
docker run -it relay-ubuntu-bionic-apt bash
$ php --ri relay
```

The installation using APT is identical for 18.04, 20.04 and 22.04.

## Debian 10

```bash
docker build --pull --tag relay-debian-buster --file debian10.Dockerfile .
docker run -it relay-debian-buster bash
$ php --ri relay
```

Relay also has an APT package compatible with Ondřej’s wonderful `ppa:ondrej/php` repository:

```bash
docker build --pull --tag relay-debian-bullseye-apt --file debian11-apt.Dockerfile .
docker run -it relay-debian-bullseye-apt bash
$ php --ri relay
```

## Amazon Linux 2

```bash
docker build --pull --tag relay-amazon2 --file amzn2.Dockerfile .
docker run -it relay-amazon2 bash
$ php --ri relay
```

## Alpine Linux 3

```bash
docker build --pull --tag relay-alpine --file alpine.Dockerfile .
docker run -it relay-alpine sh
$ php --ri relay
```

## CentOS 7

```bash
docker build --pull --tag relay-centos7 --file centos7.Dockerfile .
docker run -it relay-centos7 bash
$ php --ri relay
```

## CentOS 8

```bash
docker build --pull --tag relay-centos8 --file centos8.Dockerfile .
docker run -it relay-centos8 bash
$ php --ri relay
```
