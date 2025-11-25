FROM litespeedtech/openlitespeed:1.7.16-lsphp81

# Instead of using `php-config` let's hard code these
ENV PHP_EXT_DIR=/usr/local/lsws/lsphp81/lib/php/20210902
ENV PHP_INI_DIR=/usr/local/lsws/lsphp81/etc/php/8.1/mods-available/

RUN apt-get update && apt-get install -y \
  build-essential \
  git

# Install Relay dependencies
RUN apt-get install -y \
  libck-dev \
  libssl-dev

# Install hiredis 1.1.0+
RUN git clone --depth 1 --branch v1.1.0 https://github.com/redis/hiredis.git /tmp/hiredis && \
  cd /tmp/hiredis && \
  USE_SSL=1 make -j$(nproc) install

ARG RELAY=v0.12.1

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php8.1-debian-$PLATFORM%2Blibssl3.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.1-debian-$PLATFORM+libssl3/relay.ini" "$PHP_INI_DIR/60-relay.ini" \
  && cp "/tmp/relay-$RELAY-php8.1-debian-$PLATFORM+libssl3/relay.so" "$PHP_EXT_DIR/relay.so"

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" "$PHP_EXT_DIR/relay.so"

# Don't start `lswsctrl`
ENTRYPOINT [""]
