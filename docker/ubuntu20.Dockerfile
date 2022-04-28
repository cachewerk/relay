FROM ubuntu:20.04

RUN apt-get update
RUN apt-get upgrade -y

RUN apt-get install -y \
  ca-certificates \
  apt-transport-https \
  software-properties-common

RUN add-apt-repository ppa:ondrej/php
RUN apt-get update

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get install -y \
  php8.1-dev \
  php8.1-fpm

# Install Relay dependencies
RUN apt-get install -y \
  libev-dev \
  php8.1-msgpack \
  php8.1-igbinary

# Download Relay
RUN curl -L "https://cachewerk.s3.amazonaws.com/relay/v0.3.2/relay-v0.3.2-php8.1-debian-$(uname -m).tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN cp /tmp/relay-v0.3.2-php8.1-debian-$(uname -m)/relay.ini $(php-config --ini-dir)/30-relay.ini
RUN cp /tmp/relay-v0.3.2-php8.1-debian-$(uname -m)/relay-pkg.so $(php-config --extension-dir)/relay.so

# Inject UUID
RUN uuid=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/BIN:31415926-5358-9793-2384-626433832795/BIN:${uuid}/" $(php-config --extension-dir)/relay.so
