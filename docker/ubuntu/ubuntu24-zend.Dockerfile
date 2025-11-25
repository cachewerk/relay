FROM ubuntu:24.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update

RUN apt-get install -y \
  curl gpg

RUN curl -s https://repos.zend.com/zend-2025.key | gpg --dearmor > /usr/share/keyrings/zend.gpg \
  && echo "deb [signed-by=/usr/share/keyrings/zend.gpg] https://repos.zend.com/zendphp/deb_ubuntu2404/ zendphp non-free" > /etc/apt/sources.list.d/zendphp.list \
  && apt-get update

RUN apt-get install -y \
  php8.3-zend \
  php8.3-zend-dev

RUN apt-get install -y \
  php8.3-zend-xml

# Install Relay dependencies
RUN pecl install \
  msgpack \
  igbinary

# Fix weird Zend paths for extensions
RUN cp /usr/lib/php/8.3-zend/msgpack.so /usr/lib/php/20230831/ \
  && cp /usr/lib/php/8.3-zend/igbinary.so /usr/lib/php/20230831/ \
  && echo "extension=msgpack.so" > /etc/php/8.3/cli/conf.d/30-msgpack.ini \
  && echo "extension=igbinary.so" > /etc/php/8.3/cli/conf.d/30-igbinary.ini

ARG RELAY=v0.12.1

# Download Relay
RUN ARCH=$(uname -m | sed 's/_/-/') \
  PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-debian-$ARCH+libssl3.tar.gz" | tar xz --strip-components=1 -C /tmp

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" /tmp/relay.so

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" /etc/php/8.3/cli/conf.d/40-relay.ini \
  && cp "/tmp/relay.so" /usr/lib/php/20230831/relay.so
