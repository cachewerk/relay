# Docker

These Docker environments are concrete examples of Relay's [installation instruction](https://relay.so/docs/installation).

## EL 9 (dnf)

Using multiple version of PHP:

```bash
docker build --pull --tag relay-el9-dnf --file el9-dnf.Dockerfile .
docker run -it relay-el9-dnf bash
$ php --ri relay
```

Using a single version of PHP:

```bash
docker build --pull --tag relay-el9-single --file el9-dnf-single.Dockerfile .
docker run -it relay-el9-single bash
$ php --ri relay
```

## EL 9 (manual)

```bash
docker build --pull --tag relay-el9 --file el9.Dockerfile .
docker run -it relay-el9 bash
$ php --ri relay
```
