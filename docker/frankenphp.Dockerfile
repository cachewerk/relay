FROM dunglas/frankenphp:php8.5-trixie

# Overlay upstream; bundled version does not support ZTS artifacts yet
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install Relay dependencies
RUN apt-get update && apt-get install -y \
    libck0t64 \
    libhiredis1.1.0 \
    liblz4-1 \
    libzstd1

# Relay requires the `msgpack` and `igbinary` extension
RUN install-php-extensions igbinary msgpack

ARG RELAY=v0.21.0

# Download and install Relay
# RUN install-php-extensions "relay${RELAY:+-$RELAY}"

# --- STOP ---
# The one-liner above is what you should use. Our CI matrix needs to test
# against `dev` builds and `install-php-extensions` not does support that.

# Download Relay
RUN ARCH=$(dpkg --print-architecture | sed 's/amd64/x86-64/; s/arm64/aarch64/') \
  PHP=$(php -r 'echo substr(PHP_VERSION, 0, 3);') \
  ARTIFACT="https://builds.r2.relay.so/$RELAY/relay-$RELAY-php$PHP-debian-$ARCH+libssl3.tar.gz" \
  && curl -L $ARTIFACT | tar -xz --strip-components=1 -C /tmp

# Copy relay.{so,ini}
RUN cp "/tmp/relay.ini" "$(php-config --ini-dir)/50-relay.ini" \
  && cp "/tmp/relay-zts.so" "$(php-config --extension-dir)/relay.so"

# Inject UUID
RUN UUID=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/00000000-0000-0000-0000-000000000000/$UUID/" "$(php-config --extension-dir)/relay.so"
