FROM --platform=linux/amd64 centos:7

# It's dead, Jim
RUN sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-* && \
    sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-*

RUN yum -y update

RUN yum install -y "https://rpms.remirepo.net/enterprise/remi-release-7.rpm"

RUN yum-config-manager --disable 'remi-php*' \
  yum-config-manager --enable remi-safe

RUN yum install -y \
  php80-php-cli

ENV PATH="/opt/remi/php80/root/usr/bin/:$PATH"

# Instead of using `php-config` let's hard code these
ENV PHP_INI_DIR=/etc/opt/remi/php80/php.d/
ENV PHP_EXT_DIR=/opt/remi/php80/root/usr/lib64/php/modules/

ARG RELAY=v0.20.0

# Install Relay dependencies
RUN yum install -y \
  openssl11 \
  libzstd \
  lz4 \
  https://rpmfind.net/linux/opensuse/distribution/leap/15.5/repo/oss/x86_64/libck0-0.7.1-bp155.2.11.x86_64.rpm \
  https://rpmfind.net/linux/opensuse/distribution/leap/15.5/repo/oss/x86_64/libhiredis1_1_0-1.1.0-bp155.1.6.x86_64.rpm

# Relay requires the `msgpack` and `igbinary` extension
RUN yum install -y \
  php80-php-igbinary \
  php80-php-msgpack

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php8.0-centos7-$PLATFORM.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.0-centos7-$PLATFORM/relay.ini" "$PHP_INI_DIR/50-relay.ini" \
  && cp "/tmp/relay-$RELAY-php8.0-centos7-$PLATFORM/relay.so" "$PHP_EXT_DIR/relay.so"

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" "$PHP_EXT_DIR/relay.so"
