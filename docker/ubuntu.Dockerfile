FROM ubuntu:20.04

RUN apt update
RUN apt upgrade -y

RUN apt install -y \
  ca-certificates \
  apt-transport-https \
  software-properties-common

RUN add-apt-repository ppa:ondrej/php
RUN apt update

ARG DEBIAN_FRONTEND=noninteractive

RUN apt install -y \
  php8.0-dev \
  php8.0-fpm \
  php8.0-msgpack \
  php8.0-igbinary

# Download Relay
RUN curl -L "https://cachewerk.s3.amazonaws.com/relay/develop/relay-dev-php8.0-debian-$(uname -m).tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN cp /tmp/relay-dev-php8.0-debian-$(uname -m)/relay.ini $(php-config --ini-dir)/30-relay.ini
RUN cp /tmp/relay-dev-php8.0-debian-$(uname -m)/relay-pkg.so $(php-config --extension-dir)/relay.so

# Inject UUID
RUN uuid=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/31415926-5358-9793-2384-626433832795/${uuid}/" $(php-config --extension-dir)/relay.so
