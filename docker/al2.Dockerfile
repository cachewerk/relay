FROM amazonlinux:2

RUN yum -y install \
  gcc \
  make \
  tar \
  gzip \
  yum-utils

RUN yum remove php*
RUN amazon-linux-extras enable php8.0

RUN yum install -y \
  php-cli \
  php-pear \
  php-devel \
  openssl11

RUN pecl config-set php_ini /etc/php.ini

# Install Relay dependencies
RUN yum install -y \
  libzstd \
  lz4 \
  https://download.opensuse.org/distribution/leap/15.5/repo/oss/x86_64/libck0-0.7.1-bp155.2.11.x86_64.rpm \
  https://download.opensuse.org/distribution/leap/15.5/repo/oss/x86_64/libhiredis1_1_0-1.1.0-bp155.1.6.x86_64.rpm

# Relay requires the `msgpack` extension
RUN pecl install msgpack && \
  echo "extension = msgpack.so" > $(php-config --ini-dir)/40-msgpack.ini

# Relay requires the `igbinary` extension
RUN pecl install igbinary && \
  echo "extension = igbinary.so" > $(php-config --ini-dir)/40-igbinary.ini

ARG RELAY=v0.8.1

# Download Relay
RUN ARCH=$(uname -m | sed 's/_/-/') \
  PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-centos7-$ARCH.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN ARCH=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.0-centos7-$ARCH/relay.ini" $(php-config --ini-dir)/50-relay.ini \
  && cp "/tmp/relay-$RELAY-php8.0-centos7-$ARCH/relay.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
