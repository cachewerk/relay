FROM debian:10.10

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
  php8.1-dev

# Install Relay dependencies
RUN apt-get install -y \
  lz4 \
  zstd \
  php8.1-msgpack \
  php8.1-igbinary

ARG RELAY=v0.4.3

# Download Relay
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && wget -c "https://cachewerk.s3.amazonaws.com/relay/$RELAY/relay-$RELAY-php8.1-debian-$PLATFORM.tar.gz" -O - | tar xz -C /tmp

# Copy relay.{so,ini}
RUN PLATFORM=$(uname -m | sed 's/_/-/') \
  && cp "/tmp/relay-$RELAY-php8.1-debian-$PLATFORM/relay.ini" $(php-config --ini-dir)/30-relay.ini \
  && cp "/tmp/relay-$RELAY-php8.1-debian-$PLATFORM/relay-pkg.so" $(php-config --extension-dir)/relay.so

# Inject UUID
RUN sed -i "s/BIN:31415926-5358-9793-2384-626433832795/BIN:$(cat /proc/sys/kernel/random/uuid)/" $(php-config --extension-dir)/relay.so
