FROM debian:11

RUN sed -i 's|http://deb.debian.org/debian-security|http://snapshot.debian.org/archive/debian-security/20260831T000000Z|' /etc/apt/sources.list \
  && echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/99snapshot

# Required for libck 0.7.1, pinned so nothing else is installed from bookworm
RUN echo "deb http://deb.debian.org/debian bookworm main" > /etc/apt/sources.list.d/bookworm.list \
  && printf 'Package: *\nPin: release n=bookworm\nPin-Priority: 100\n' > /etc/apt/preferences.d/bookworm

RUN apt-get update

RUN apt-get install -y \
  ca-certificates \
  wget

# Debian 11's own PHP 7.4, as Ondřej’s repository no longer serves bullseye
RUN apt-get install -y \
  php7.4-dev

# Install Relay dependencies
RUN apt-get install -y \
  libck-dev/bookworm

# Install optional extensions and compression libraries
RUN apt-get install -y \
  lz4 \
  zstd \
  php-msgpack \
  php-igbinary

# Install Relay dependency (hiredis)
RUN wget -qO- https://github.com/redis/hiredis/archive/refs/tags/v1.2.0.tar.gz | tar -xzC /tmp \
  && USE_SSL=1 make -C /tmp/hiredis-1.2.0 install

ARG RELAY=v0.62.0

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && wget -c "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php7.4-debian-$PLATFORM.tar.gz" -O - | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php7.4-debian-$PLATFORM/relay.ini" $(php-config --ini-dir)/30-relay.ini \
  && cp "/tmp/relay-$RELAY-php7.4-debian-$PLATFORM/relay.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
