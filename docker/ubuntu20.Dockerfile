FROM ubuntu:20.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update
RUN apt-get upgrade -y

RUN apt-get install -y \
  ca-certificates \
  apt-transport-https

RUN apt-get install -y \
  php-dev \
  php-fpm

# Install Relay dependencies
RUN apt-get install -y \
  libev-dev \
  php-msgpack \
  php-igbinary

ENV RELAY=v0.3.2

# Download Relay
RUN PLATFORM=`uname -m` \
  && curl -L "https://cachewerk.s3.amazonaws.com/relay/$RELAY/relay-$RELAY-php7.4-debian-${PLATFORM/_/-}.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=`uname -m` \
  && cp "/tmp/relay-$RELAY-php7.4-debian-${PLATFORM/_/-}/relay.ini" $(php-config --ini-dir)/30-relay.ini \
  && cp "/tmp/relay-$RELAY-php7.4-debian-${PLATFORM/_/-}/relay-pkg.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN uuid=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/BIN:31415926-5358-9793-2384-626433832795/BIN:${uuid}/" $(php-config --extension-dir)/relay.so
