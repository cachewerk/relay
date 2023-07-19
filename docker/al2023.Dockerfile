FROM amazonlinux:2023

RUN dnf install -y \
  php-cli \
  php-pear \
  php-devel \
  gzip

# Relay requires the `msgpack` extension
RUN pecl install msgpack && \
  echo "extension = msgpack.so" > $(php-config --ini-dir)/40-msgpack.ini

# Relay requires the `igbinary` extension
RUN pecl install igbinary && \
  echo "extension = igbinary.so" > $(php-config --ini-dir)/40-igbinary.ini

ARG RELAY=v0.6.5

RUN ARCH=$(uname -m | sed 's/_/-/') \
  PHP_VERSION=$(php -r 'echo phpversion();' | sed 's/\.[0-9]*$//') \
  ARTIFACT="https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP_VERSION-el9-$ARCH.tar.gz" \
  && curl -sfSL $ARTIFACT | tar xz --strip-components=1 -C /tmp

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" $(php-config --ini-dir)/50-relay.ini \
  && cp "/tmp/relay-pkg.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
