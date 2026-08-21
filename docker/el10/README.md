# Docker

These Docker environments are concrete examples of Relay's [installation instruction](https://relay.so/docs/installation).

## EL 10 (dnf)

Using multiple version of PHP:

```bash
docker build --pull --tag relay-el10-dnf --file el10-dnf.Dockerfile .
docker run -it relay-el10-dnf bash
$ php --ri relay
```

Using a single version of PHP:

```bash
docker build --pull --tag relay-el10-single --file el10-dnf-single.Dockerfile .
docker run -it relay-el10-single bash
$ php --ri relay
```

## EL 10 (manual)

```bash
docker build --pull --tag relay-el10 --file el10.Dockerfile .
docker run -it relay-el10 bash
$ php --ri relay
```
