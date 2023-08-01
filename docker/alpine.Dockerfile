FROM alpine:3.15

RUN apk update

RUN apk add \
  curl \
  php8 \
  php8-dev \
  php8-pecl-msgpack \
  php8-pecl-igbinary \
  && ln -s /usr/bin/php8 /usr/bin/php \
  && ln -s /usr/bin/php-config8 /usr/bin/php-config

# Install Relay dependencies
RUN apk add \
  lz4-libs \
  zstd-libs

ARG RELAY=v0.6.6

RUN set -eux \
  && apk add --no-cache --virtual .build-deps \
  gcc \
  make \
  musl-dev \
  openssl-dev \
  # Build and install hiredis (>= 1.1.0) with SSL support \
  && curl -L https://github.com/redis/hiredis/archive/refs/tags/v1.2.0.tar.gz | tar -xz \
  && PREFIX=/usr USE_SSL=1 make -C hiredis-1.2.0 install \
  # Build and install concurrencykit \
  && curl -L https://github.com/concurrencykit/ck/archive/refs/tags/0.7.1.tar.gz | tar -xz \
  && cd ck-0.7.1 \
  && ./configure \
  && make install \
  # Cleanup \
  && apk del .build-deps

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php8.0-alpine3.9-$PLATFORM.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.0-alpine3.9-$PLATFORM/relay.ini" $(php-config --ini-dir)/60_relay.ini \
  && cp "/tmp/relay-$RELAY-php8.0-alpine3.9-$PLATFORM/relay.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
