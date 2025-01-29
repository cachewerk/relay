FROM archlinux:latest

RUN pacman -Syu --noconfirm \
  && pacman -S --noconfirm php

ARG RELAY=v0.10.0

# Install Relay dependencies
RUN pacman -S --noconfirm \
  base-devel \
  # hiredis \
  libck \
  lz4

# Install hiredis-ssl
RUN curl -L https://github.com/redis/hiredis/archive/refs/tags/v1.2.0.tar.gz | tar -xzC /tmp \
  && PREFIX=/usr USE_SSL=1 make -C /tmp/hiredis-1.2.0 install

# Install php-pear
RUN curl -L https://aur.archlinux.org/cgit/aur.git/snapshot/php-pear.tar.gz | sudo -u nobody tar -xzC /tmp \
  && pushd /tmp/php-pear \
  && sudo -u nobody makepkg --skippgpcheck \
  && pacman -U --noconfirm php-pear-1:1.10.23-2-any.pkg.tar.zst \
  && popd

# Relay requires the `msgpack` extension
RUN pecl install msgpack \
  && echo "extension = msgpack.so" > $(php-config --ini-dir)/msgpack.ini

# Relay requires the `igbinary` extension
RUN pecl install igbinary \
  && echo "extension = igbinary.so" > $(php-config --ini-dir)/igbinary.ini

# Download Relay
RUN ARCH=$(uname -m | sed 's/_/-/') \
  PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-el9-$ARCH.tar.gz" | tar -xz --strip-components=1 -C /tmp

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" "$(php-config --ini-dir)/relay.ini" \
  && cp "/tmp/relay.so" "$(php-config --extension-dir)/relay.so"

# Inject UUID
RUN UUID=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/00000000-0000-0000-0000-000000000000/$UUID/" "$(php-config --extension-dir)/relay.so"
