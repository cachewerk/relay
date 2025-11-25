FROM litespeedtech/litespeed:6.0.8-lsphp74

# Instead of using `php-config` let's hard code these
ENV PHP_EXT_DIR=/usr/local/lsws/lsphp74/lib/php/20190902/
ENV PHP_INI_DIR=/usr/local/lsws/lsphp74/etc/php/7.4/mods-available/

RUN apt-get update && apt-get install -y \
  build-essential

# Install Relay dependencies
RUN apt-get install -y \
  libck-dev \
  libssl-dev

# Install Relay dependency (hiredis)
RUN curl -L https://github.com/redis/hiredis/archive/refs/tags/v1.2.0.tar.gz | tar -xzC /tmp \
  && USE_SSL=1 make -C /tmp/hiredis-1.2.0 install

ARG RELAY=v0.12.1

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php7.4-debian-$PLATFORM.tar.gz" | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php7.4-debian-$PLATFORM/relay.ini" "$PHP_INI_DIR/60-relay.ini" \
  && cp "/tmp/relay-$RELAY-php7.4-debian-$PLATFORM/relay.so" "$PHP_EXT_DIR/relay.so"

# Inject UUID
RUN sed -i "s/00000000-0000-0000-0000-000000000000/$(cat /proc/sys/kernel/random/uuid)/" "$PHP_EXT_DIR/relay.so"

# Don't start `lswsctrl`
ENTRYPOINT [""]
