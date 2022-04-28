FROM debian:10.10

RUN apt-get update
RUN apt-get upgrade -y

RUN apt-get install -y \
  gnupg \
  lsb-release \
  apt-transport-https \
  ca-certificates \
  software-properties-common \
  wget

RUN wget -q https://packages.sury.org/php/apt.gpg -O- | apt-key add -
RUN add-apt-repository "deb https://packages.sury.org/php/ $(lsb_release -sc) main"
RUN apt-get update

# Fix `php-config` link to `sed`
RUN ln -s /bin/sed /usr/bin/sed

# Relay requires the `msgpack` and `igbinary` extensions
RUN apt-get install -y \
  php8.1-dev \
  php8.1-msgpack \
  php8.1-igbinary

# Install Relay dependencies
RUN apt-get install -y \
  libev-dev

# Download Relay
RUN wget -c "https://cachewerk.s3.amazonaws.com/relay/v0.3.2/relay-v0.3.2-php8.1-debian-$(uname -m).tar.gz" -O - | tar xz -C /tmp

# Copy relay.{so,ini}
RUN cp /tmp/relay-v0.3.2-php8.1-debian-$(uname -m)/relay.ini $(php-config --ini-dir)/30-relay.ini
RUN cp /tmp/relay-v0.3.2-php8.1-debian-$(uname -m)/relay-pkg.so $(php-config --extension-dir)/relay.so

# Inject UUID
RUN uuid=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/BIN:31415926-5358-9793-2384-626433832795/BIN:${uuid}/" $(php-config --extension-dir)/relay.so
