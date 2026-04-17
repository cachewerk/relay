# Docker

These Docker environments are concrete examples of Relay's [installation instruction](https://relay.so/docs/installation).

## Versions / Nightly builds

You may specify the Relay version/build for non-package (APT/YUM) Docker examples:

```bash
docker build --pull --tag relay-alpine --file alpine.Dockerfile --build-arg RELAY=v0.9.1 .
```

To install the nightly developments builds use the `dev` version:

```bash
docker build --pull --tag relay-alpine --file alpine.Dockerfile --build-arg RELAY=dev .
```

## Amazon Linux 2

```bash
docker build --pull --tag relay-amazon2 --file al2.Dockerfile .
docker run -it relay-amazon2 bash
$ php --ri relay
```

## Amazon Linux 2023

```bash
docker build --pull --tag relay-amazon2023 --file al2023.Dockerfile .
docker run -it relay-amazon2023 bash
$ php --ri relay
```

## Alpine Linux 3

```bash
docker build --pull --tag relay-alpine --file alpine.Dockerfile .
docker run -it relay-alpine sh
$ php --ri relay
```

## Apache 2

```bash
docker build --pull --tag relay-apache2 --file apache2.Dockerfile .
docker run -it relay-apache2 bash
$ php --ri relay
```

## Arch Linux

```bash
docker build --pull --tag relay-arch --file arch.Dockerfile .
docker run -it relay-arch bash
$ php --ri relay
```

## FrankenPHP

```bash
docker build --pull --tag relay-frankenphp --file frankenphp.Dockerfile --build-arg RELAY=dev .
docker run -it relay-frankenphp bash
$ php --ri relay
```

## Linux Mint 21

```bash
docker build --pull --tag relay-mint21 --file mint21.Dockerfile .
docker run -it relay-mint21 bash
$ php --ri relay
```

## SUSE Linux Enterprise 15

```bash
docker build --pull --tag relay-sle15 --file sle15.Dockerfile .
docker run -it relay-sle15 bash
$ php --ri relay
```

## PHP Images

This image uses `mlocati/php-extension-installer` to install the extension.

```bash
docker build --pull --tag relay-php-cli --file php-cli.Dockerfile .
docker run -it relay-php-cli bash
$ php --ri relay
```
