FROM debian:buster

RUN apt update
RUN apt upgrade -y

RUN apt install -y \
  lsb-release \
  apt-transport-https \
  ca-certificates \
  wget

RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list

RUN apt update
RUN apt install -y \
  php8.1-dev \
  php8.1-fpm \
  php8.1-msgpack \
  php8.1-igbinary

# Install Relay dependencies
RUN apt install -y \
  libev-dev

# Download Relay
RUN wget -c "https://cachewerk.s3.amazonaws.com/relay/develop/relay-dev-php8.1-debian-$(uname -m).tar.gz" -O - | tar xz -C /tmp

# Copy relay.{so,ini}
RUN cp /tmp/relay-dev-php8.1-debian-$(uname -m)/relay.ini $(php-config --ini-dir)/30-relay.ini
RUN cp /tmp/relay-dev-php8.1-debian-$(uname -m)/relay-pkg.so $(php-config --extension-dir)/relay.so

# Inject UUID
RUN uuid=$(cat /proc/sys/kernel/random/uuid) \
  && sed -i "s/31415926-5358-9793-2384-626433832795/${uuid}/" $(php-config --extension-dir)/relay.so
