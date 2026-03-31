FROM php:8.2-cli

ARG RELAY=^0.20.0

# Install Relay's required libraries and uuidgen for PIE's config.m4
RUN apt-get update && apt-get install -y \
  uuid-runtime \
  libhiredis-dev \
  libck-dev \
  && rm -rf /var/lib/apt/lists/*

# Install igbinary and msgpack (required by Relay)
RUN pecl install igbinary msgpack \
  && docker-php-ext-enable igbinary msgpack

# Install PIE
RUN curl -fsSL https://github.com/php/pie/releases/latest/download/pie.phar -o /usr/local/bin/pie \
  && chmod +x /usr/local/bin/pie

# Install Relay
RUN pie install "cachewerk/ext-relay:$RELAY"
