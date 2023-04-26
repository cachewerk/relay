# Docker (CentOS 7)

These Docker environments are concrete examples of Relay's [installation instruction](https://relay.so/docs/installation).

## CentOS 7 (yum)

Using multiple version of PHP:

```bash
docker build --pull --tag relay-centos7-yum --file centos7-yum.Dockerfile .
docker run -it relay-centos7-yum bash
$ php --ri relay
```

Using a single version of PHP:

```bash
docker build --pull --tag relay-centos7-single --file centos7-yum-single.Dockerfile .
docker run -it relay-centos7-single bash
$ php --ri relay
```

## CentOS 7 (manual)

```bash
docker build --pull --tag relay-centos7 --file centos7.Dockerfile .
docker run -it relay-centos7 bash
$ php --ri relay
```
