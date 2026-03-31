FROM php:8.2-cli

ARG RELAY=0.21.0

# Install PIE
RUN curl -fsSL https://github.com/php/pie/releases/latest/download/pie.phar -o /usr/local/bin/pie \
  && chmod +x /usr/local/bin/pie

# Install Relay
RUN pie install "cachewerk/ext-relay:$RELAY"
