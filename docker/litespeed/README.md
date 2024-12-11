# Docker

These Docker environments are concrete examples of Relay's [installation instruction](https://relay.so/docs/installation).

## LiteSpeed

```bash
docker build --pull --tag relay-ls --file litespeed.Dockerfile .
docker run -it relay-ls bash
$ php --ri relay
```

## OpenLiteSpeed

```bash
docker build --pull --tag relay-ols --file openlitespeed.Dockerfile .
docker run -it relay-ols bash
$ php --ri relay
```
