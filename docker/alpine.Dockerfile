FROM alpine:3.18

RUN apk update

RUN apk add \
  curl \
  php81 \
  php81-dev

# Install Relay dependencies
RUN apk add \
  ck \
  hiredis \
  hiredis-ssl \
  lz4-libs \
  zstd-libs \
  php81-pecl-msgpack \
  php81-pecl-igbinary

ARG RELAY=v0.6.8

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php8.1-alpine3.17-$PLATFORM.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.1-alpine3.17-$PLATFORM/relay.ini" $(php-config --ini-dir)/60_relay.ini \
  && cp "/tmp/relay-$RELAY-php8.1-alpine3.17-$PLATFORM/relay.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
