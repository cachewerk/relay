# Docker (CentOS 8)

These Docker environments are concrete examples of Relay's [installation instruction](https://relay.so/docs/installation).

## CentOS 8 (dnf)

Using multiple version of PHP:

```bash
docker build --pull --tag relay-centos8-dnf --file centos8-dnf.Dockerfile .
docker run -it relay-centos8-dnf bash
$ php --ri relay
```

Using a single version of PHP:

```bash
docker build --pull --tag relay-centos8-single --file centos8-dnf-single.Dockerfile .
docker run -it relay-centos8-single bash
$ php --ri relay
```

## CentOS 8 (manual)

```bash
docker build --pull --tag relay-centos8 --file centos8.Dockerfile .
docker run -it relay-centos8 bash
$ php --ri relay
```

## Rocky Linux 8

```bash
docker build --pull --tag relay-rocky8 --file rocky8.Dockerfile .
docker run -it relay-rocky8 bash
$ php --ri relay
```
