FROM ubuntu:24.04

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

ARG RELAY=v0.9.1

# Download Relay
RUN ARCH=$(uname -m | sed 's/_/-/') \
  PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-debian-$ARCH+libssl3.tar.gz" | tar xz --strip-components=1 -C /tmp

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" $(php-config --ini-dir)/30-relay.ini \
  && cp "/tmp/relay-pkg.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
