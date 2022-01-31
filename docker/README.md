# Docker

These Docker environments are concrete examples of Relay's [installation instruction](https://relaycache.com/docs/installation).

## Ubuntu (PHP 8.1)

```bash
docker build --pull --tag relay-ubuntu --file ubuntu.Dockerfile .
docker run -it relay-ubuntu bash
$ php --ri relay
```

Relay also has an APT package compatible with Ondřej’s wonderful `ppa:ondrej/php` repository:

```bash
docker build --pull --tag relay-ubuntu-apt --file ubuntu-apt.Dockerfile .
docker run -it relay-ubuntu-apt bash
$ php --ri relay
```

## Debian (PHP 8.1)

```bash
docker build --pull --tag relay-debian --file debian.Dockerfile .
docker run -it relay-debian bash
$ php --ri relay
```

## Amazon Linux 2 (PHP 8.0)

```bash
docker build --pull --tag relay-amazon2 --file amzn2.Dockerfile .
docker run -it relay-amazon2 bash
$ php --ri relay
```

## Alpine Linux 3 (PHP 8.0)

```bash
docker build --pull --tag relay-alpine --file alpine.Dockerfile .
docker run -it relay-alpine sh
$ php --ri relay
```

## CentOS 7 (PHP 8.0)

```bash
docker build --pull --tag relay-centos7 --file centos7.Dockerfile .
docker run -it relay-centos7 bash
$ php --ri relay
```

## CentOS 8 (PHP 8.0)

```bash
docker build --pull --tag relay-centos8 --file centos8.Dockerfile .
docker run -it relay-centos8 bash
$ php --ri relay
```
