FROM amazonlinux:2023

RUN dnf install -y \
  php-cli \
  php-pear \
  php-devel \
  gzip

# Install optional `igbinary` extension
RUN pecl install igbinary && \
  echo "extension = igbinary.so" > $(php-config --ini-dir)/40-igbinary.ini

ARG RELAY=v0.30.0

# Install Relay dependencies (hiredis)
RUN dnf install -y \
  gcc make openssl-devel \
  && curl -fsSL https://github.com/redis/hiredis/archive/refs/tags/v1.2.0.tar.gz | tar xz -C /tmp \
  && make -C /tmp/hiredis-1.2.0 USE_SSL=1 PREFIX=/usr LIBRARY_PATH=lib64 install \
  && ldconfig

# Install Relay dependencies (libck)
RUN dnf install -y --nogpgcheck \
  --repofrompath opensuse,http://download.opensuse.org/pub/opensuse/distribution/leap/15.5/repo/oss/ \
  libck0

RUN ARCH=$(uname -m | sed 's/_/-/') \
  PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  ARTIFACT="https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-el9-$ARCH.tar.gz" \
  && curl -sfSL $ARTIFACT | tar xz --strip-components=1 -C /tmp

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" $(php-config --ini-dir)/50-relay.ini \
  && cp "/tmp/relay.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
