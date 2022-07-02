FROM centos:8.4.2105

# CentOS Linux 8 is EOL (https://stackoverflow.com/a/70930049)
RUN sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-Linux-*

RUN dnf install -y "https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm"
RUN dnf install -y "https://rpms.remirepo.net/enterprise/remi-release-8.4.rpm"
RUN dnf install -y yum-utils

RUN dnf install -y \
  php80 \
  php80-php-cli \
  php80-php-fpm

ENV PATH="/opt/remi/php80/root/usr/bin/:$PATH"

# Instead of using `php-config` let's hard code these
ENV PHP_INI_DIR=/etc/opt/remi/php80/php.d/
ENV PHP_EXT_DIR=/opt/remi/php80/root/usr/lib64/php/modules/

ARG RELAY=v0.4.3

# Relay requires the `msgpack` and `igbinary` extension
RUN yum install -y \
  php80-php-igbinary \
  php80-php-msgpack

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && curl -L "https://cachewerk.s3.amazonaws.com/relay/$RELAY/relay-$RELAY-php8.0-centos8-$PLATFORM.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.0-centos8-$PLATFORM/relay.ini" "$PHP_INI_DIR/50-relay.ini" \
  && cp "/tmp/relay-$RELAY-php8.0-centos8-$PLATFORM/relay-pkg.so" "$PHP_EXT_DIR/relay.so"

# Inject UUID
RUN sed -i "s/BIN:31415926-5358-9793-2384-626433832795/BIN:$(cat /proc/sys/kernel/random/uuid)/" "$PHP_EXT_DIR/relay.so"
