FROM rockylinux/rockylinux:9

ARG DEBIAN_FRONTEND=noninteractive

RUN dnf -y install https://rpms.remirepo.net/enterprise/remi-release-9.rpm

RUN dnf -y module reset php
RUN dnf -y module install php:remi-8.2

RUN dnf -y update
RUN dnf -y install php-cli

# Instead of using `php-config` let's hard code these
ENV PHP_INI_DIR=/etc/php.d/
ENV PHP_EXT_DIR=/usr/lib64/php/modules

ARG RELAY=v0.8.0

# Relay requires the `msgpack` and `igbinary` extension
RUN dnf -y install \
  php-msgpack \
  php-igbinary

# Install Relay dependencies
RUN yum install -y --nogpgcheck \
  --repofrompath opensuse,http://download.opensuse.org/pub/opensuse/distribution/leap/15.5/repo/oss/ \
  libhiredis1_1_0 \
  libck0

# Download Relay
RUN ARCH=$(uname -m | sed 's/_/-/') \
  PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  ARTIFACT="https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-el9-$ARCH.tar.gz" \
  && curl -L $ARTIFACT | tar -xz --strip-components=1 -C /tmp

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" "$PHP_INI_DIR/50-relay.ini" \
  && cp "/tmp/relay.so" "$PHP_EXT_DIR/relay.so"

# Inject UUID
RUN UUID=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/00000000-0000-0000-0000-000000000000/$UUID/" "$PHP_EXT_DIR/relay.so"
