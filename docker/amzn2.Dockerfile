FROM amazonlinux:2

RUN yum -y install \
  gcc \
  make \
  tar \
  yum-utils

RUN yum remove php*
RUN amazon-linux-extras enable php8.0

RUN yum install -y \
 php-cli \
 php-fpm \
 php-pear \
 php-devel \
 openssl11 \
 libev-devel \
 libzstd-devel

RUN pecl config-set php_ini /etc/php.ini

# Relay requires the `msgpack` extension
RUN pecl install msgpack && \
  echo "extension = msgpack.so" > $(php-config --ini-dir)/40-msgpack.ini

# Relay requires the `igbinary` extension
RUN pecl install igbinary && \
  echo "extension = igbinary.so" > $(php-config --ini-dir)/40-igbinary.ini

ENV RELAY=v0.3.2

# Download Relay
RUN PLATFORM=`uname -m` \
  && curl -L "https://cachewerk.s3.amazonaws.com/relay/$RELAY/relay-$RELAY-php8.0-centos7-${PLATFORM/_/-}.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=`uname -m` \
  && cp "/tmp/relay-$RELAY-php8.0-centos7-${PLATFORM/_/-}/relay.ini" $(php-config --ini-dir)/50-relay.ini \
  && cp "/tmp/relay-$RELAY-php8.0-centos7-${PLATFORM/_/-}/relay-pkg.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN uuid=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/BIN:31415926-5358-9793-2384-626433832795/BIN:${uuid}/" $(php-config --extension-dir)/relay.so
