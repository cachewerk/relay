FROM debian:11

# Debian 11 reached end of life on 2026-08-31. Its `bullseye-security` pool is
# being torn down and now serves intermittent 404s, so pin the base system to
# the frozen archive instead. `bullseye-security` is dropped rather than
# repointed, as it has not been published on archive.debian.org yet.
RUN printf '%s\n' \
      'deb http://archive.debian.org/debian bullseye main' \
      'deb http://archive.debian.org/debian bullseye-updates main' \
      > /etc/apt/sources.list

RUN apt-get update

RUN apt-get install -y \
  wget \
  gnupg \
  lsb-release

RUN wget -q "https://packages.sury.org/php/apt.gpg" -O /usr/share/keyrings/ondrej.gpg
RUN echo "deb [signed-by=/usr/share/keyrings/ondrej.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/ondrej.list > /dev/null
RUN apt-get update

RUN apt-get install -y \
  php8.2-fpm

# Add Relay repository
RUN wget -q "https://repos.r2.relay.so/key.gpg" -O- | gpg --dearmor -o /usr/share/keyrings/cachewerk.gpg
RUN echo "deb [signed-by=/usr/share/keyrings/cachewerk.gpg] https://repos.r2.relay.so/deb $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/cachewerk.list > /dev/null
RUN apt-get update

# Install Relay
RUN apt-get install -y \
  php8.2-relay

## If no specific PHP version is installed just omit the version number:

# RUN apt-get install -y \
#  php-dev \
#  php-fpm \
#  php-relay
