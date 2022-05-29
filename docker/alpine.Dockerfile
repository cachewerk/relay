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
  libev \
  lz4-libs \
  zstd-libs

ENV RELAY=v0.3.2

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && curl -L "https://cachewerk.s3.amazonaws.com/relay/$RELAY/relay-$RELAY-php8.0-alpine-$PLATFORM.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.0-alpine-$PLATFORM/relay.ini" $(php-config --ini-dir)/60_relay.ini \
  && cp "/tmp/relay-$RELAY-php8.0-alpine-$PLATFORM/relay-pkg.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN uuid=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/BIN:31415926-5358-9793-2384-626433832795/BIN:${uuid}/" $(php-config --extension-dir)/relay.so
