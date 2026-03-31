FROM php:8.5-cli

# Install PIE
RUN apt-get update && apt-get install -y git uuid-runtime \
  && curl -fsSL https://github.com/php/pie/releases/latest/download/pie.phar -o /usr/local/bin/pie \
  && chmod +x /usr/local/bin/pie

# Install Relay dependencies
RUN apt-get install -y \
  libhiredis-dev \
  libck-dev \
  libssl-dev

# Install igbinary and msgpack
RUN pecl install igbinary msgpack \
  && docker-php-ext-enable igbinary msgpack

# Install Relay
RUN pie install cachewerk/ext-relay
