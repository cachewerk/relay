# Docker

These Docker environments are concrete examples of Relay's [installation instruction](https://relay.so/docs/installation).

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

The installation using APT is identical for 16.04, 18.04, 20.04 and 22.04.

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

See [centos7.Dockerfile](/docker/centos7.Dockerfile) for the manual installation.

## CentOS 8

```bash
docker build --pull --tag relay-centos8 --file centos8-dnf.Dockerfile .
docker run -it relay-centos8 bash
$ php --ri relay
```

See [centos8.Dockerfile](/docker/centos8.Dockerfile) for the manual installation.

## Rocky Linux 8

```bash
docker build --pull --tag relay-rocky8 --file rocky8-dnf.Dockerfile .
docker run -it relay-rocky8 bash
$ php --ri relay
```

See [rocky8.Dockerfile](/docker/rocky8.Dockerfile) for the manual installation.

## LiteSpeed

```bash
docker build --pull --tag relay-litespeed --file litespeed.Dockerfile .
docker run -it relay-litespeed bash
$ php --ri relay
```

For OpenLiteSpeed, see [openlitespeed.Dockerfile](/docker/openlitespeed.Dockerfile).

## Apache 2

```bash
docker build --pull --tag relay-apache2 --file apache2.Dockerfile .
docker run -it relay-apache2 bash
$ php --ri relay
```

## PHP Images

This image uses `mlocati/php-extension-installer` to install the extension.

```bash
docker build --pull --tag relay-php81 --file php81-cli.Dockerfile .
docker run -it relay-php81 bash
$ php --ri relay
```

## Versions / Nightly builds

You may specify the Relay version/build for non-package (APT/YUM) Docker examples:

```
docker build --pull --tag relay-alpine --file alpine.Dockerfile --build-arg RELAY=v0.6.0 .
```

To install the nightly developments builds use the `dev` version:

```
docker build --pull --tag relay-alpine --file alpine.Dockerfile --build-arg RELAY=dev .
```
