FROM debian:11

RUN apt-get update

RUN apt-get install -y \
  gnupg \
  lsb-release \
  apt-transport-https \
  ca-certificates \
  software-properties-common \
  wget

RUN wget -q "https://packages.sury.org/php/apt.gpg" -O- | apt-key add -
RUN add-apt-repository "deb https://packages.sury.org/php/ $(lsb_release -sc) main"
RUN apt-get update

# Fix `php-config` link to `sed`
RUN ln -s /bin/sed /usr/bin/sed

RUN apt-get install -y \
  php8.4-dev

# Install Relay dependencies
RUN apt-get install -y \
  lz4 \
  zstd \
  php8.4-msgpack \
  php8.4-igbinary

ARG RELAY=v0.9.1

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && wget -c "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php8.4-debian-$PLATFORM+libssl3.tar.gz" -O - | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.4-debian-$PLATFORM/relay.ini" $(php-config --ini-dir)/30-relay.ini \
  && cp "/tmp/relay-$RELAY-php8.4-debian-$PLATFORM/relay.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so

# Ensure Relay is correctly installed
RUN php --ri relay
