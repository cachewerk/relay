FROM ubuntu:20.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update

RUN apt-get install -y \
  ca-certificates \
  apt-transport-https

RUN apt-get install -y \
  php-dev \
  php-fpm

# Install Relay dependencies
RUN apt-get install -y \
  php-msgpack \
  php-igbinary

ARG RELAY=v0.4.3

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && curl -L "https://cachewerk.s3.amazonaws.com/relay/$RELAY/relay-$RELAY-php7.4-debian-$PLATFORM.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php7.4-debian-$PLATFORM/relay.ini" $(php-config --ini-dir)/30-relay.ini \
  && cp "/tmp/relay-$RELAY-php7.4-debian-$PLATFORM/relay-pkg.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/BIN:31415926-5358-9793-2384-626433832795/BIN:$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
