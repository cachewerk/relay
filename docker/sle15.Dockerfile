FROM registry.suse.com/suse/sle15:latest

RUN zypper addrepo http://download.opensuse.org/distribution/leap/15.5/repo/oss/ OSS

RUN zypper --gpg-auto-import-keys update \
  && zypper install -y \
    awk \
    make \
    openssl-3 \
    php8-pecl \
    php8-devel

ARG RELAY=v0.6.6

# Install Relay dependencies
RUN zypper install -y \
  libck0 \
  libhiredis1_1_0

# Relay requires the `msgpack` extension
RUN pecl install msgpack && \
  echo "extension = msgpack.so" > $(php-config --ini-dir)/msgpack.ini

# Relay requires the `igbinary` extension
RUN pecl install igbinary && \
  echo "extension = igbinary.so" > $(php-config --ini-dir)/igbinary.ini

# Download Relay
RUN ARCH=$(uname -m | sed 's/_/-/') \
  PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  ARTIFACT="https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-centos8-$ARCH.tar.gz" \
  && curl -L $ARTIFACT | tar -xz --strip-components=1 -C /tmp

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" "$(php-config --ini-dir)/relay.ini" \
  && cp "/tmp/relay.so" "$(php-config --extension-dir)/relay.so"

# Inject UUID
RUN UUID=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/00000000-0000-0000-0000-000000000000/$UUID/" "$(php-config --extension-dir)/relay.so"
