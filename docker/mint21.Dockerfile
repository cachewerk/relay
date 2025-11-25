FROM linuxmintd/mint21.2-amd64

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update

RUN add-apt-repository -y ppa:ondrej/php

RUN apt-get install -y \
  curl \
  php-dev

# Install Relay dependencies
RUN apt-get install -y \
  php-msgpack \
  php-igbinary

ARG RELAY=v0.12.1

# Download Relay
RUN PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-debian-x86-64+libssl3.tar.gz" | tar xz --strip-components=1 -C /tmp

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" $(php-config --ini-dir)/30-relay.ini \
  && cp "/tmp/relay.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
