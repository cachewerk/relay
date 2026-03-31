FROM php:8.5-cli

# Install Relay's required libraries and uuidgen for PIE's config.m4
RUN apt-get update && apt-get install -y \
  git \
  uuid-runtime \
  libhiredis-dev \
  libck-dev \
  libssl-dev \
  && rm -rf /var/lib/apt/lists/*

# Install igbinary and msgpack (required by Relay)
RUN pecl install igbinary msgpack \
  && docker-php-ext-enable igbinary msgpack

# Install PIE
RUN curl -fsSL https://github.com/php/pie/releases/latest/download/pie.phar -o /usr/local/bin/pie \
  && chmod +x /usr/local/bin/pie

# Install Relay
RUN pie install cachewerk/ext-relay
