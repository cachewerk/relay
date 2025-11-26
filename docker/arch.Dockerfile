FROM archlinux:base-devel

ARG RELAY=v0.12.1

RUN pacman-key --init \
  && pacman -Syu --noconfirm \
  && pacman -S --noconfirm archlinux-keyring \
  php \
  php-igbinary \
  hiredis \
  libck \
  lz4

# Install Relay dependency (hiredis)
RUN pacman -U --noconfirm \
  'https://archive.archlinux.org/packages/h/hiredis/hiredis-1.2.0-3-x86_64.pkg.tar.zst'

# Install PECL
RUN curl -L -o /tmp/go-pear.phar https://pear.php.net/go-pear.phar && \
  php /tmp/go-pear.phar

# Relay requires the `msgpack` extension
RUN pecl install msgpack \
  && echo "extension = msgpack.so" > $(php-config --ini-dir)/10-msgpack.ini

# Download Relay
RUN ARCH=$(uname -m | sed 's/_/-/') \
  PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  && curl -L "https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-el9-$ARCH.tar.gz" | tar -xz --strip-components=1 -C /tmp

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" "$(php-config --ini-dir)/20-relay.ini" \
  && cp "/tmp/relay.so" "$(php-config --extension-dir)/relay.so"

# Inject UUID
RUN UUID=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/00000000-0000-0000-0000-000000000000/$UUID/" "$(php-config --extension-dir)/relay.so"
