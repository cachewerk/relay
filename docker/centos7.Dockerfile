FROM centos:7

RUN yum install -y "https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm"
RUN yum install -y "https://rpms.remirepo.net/enterprise/remi-release-7.rpm"
RUN yum install -y yum-utils

RUN yum-config-manager --disable 'remi-php*' \
  yum-config-manager --enable remi-safe

RUN yum install -y php80 \
  php80-php-cli \
  php80-php-fpm

ENV PATH="/opt/remi/php80/root/usr/bin/:$PATH"

ARG RELAY=v0.4.0

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && curl -L "https://cachewerk.s3.amazonaws.com/relay/$RELAY/relay-$RELAY-php8.0-centos7-$PLATFORM.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.0-centos7-$PLATFORM/relay.ini" $(php-config --ini-dir)/50-relay.ini \
  && cp "/tmp/relay-$RELAY-php8.0-centos7-$PLATFORM/relay-pkg.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/BIN:31415926-5358-9793-2384-626433832795/BIN:$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
