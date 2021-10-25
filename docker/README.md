# Docker environments

## Ubuntu (PHP 8.0)

### Using APT

```bash
docker build --pull --tag relay-ubuntu --file ubuntu.Dockerfile .
docker run -it relay-ubuntu bash
```

### Manual

```bash
docker build --pull --tag relay-ubuntu-apt --file ubuntu-apt.Dockerfile .
docker run -it relay-ubuntu-apt bash
```

## Amazon Linux 2 (PHP 8.0)

```bash
docker build --pull --tag relay-amazon2 --file amzn2.Dockerfile .
docker run -it relay-amazon2 bash
```

## CentOS 7 (PHP 8.0)

```bash
docker build --pull --tag relay-centos7 --file centos7.Dockerfile .
docker run -it relay-centos7 bash
```

## CentOS 8 (PHP 8.0)

```bash
docker build --pull --tag relay-centos8 --file centos8.Dockerfile .
docker run -it relay-centos8 bash
```
