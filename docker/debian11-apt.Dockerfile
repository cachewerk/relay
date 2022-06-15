FROM debian:11

RUN apt-get update

RUN apt-get install -y \
  wget \
  gnupg \
  lsb-release \
  ca-certificates \
  apt-transport-https \
  software-properties-common

RUN wget -q "https://packages.sury.org/php/apt.gpg" -O- | apt-key add -
RUN add-apt-repository "deb https://packages.sury.org/php/ $(lsb_release -sc) main"
RUN apt-get update

RUN apt-get install -y \
  php8.1-fpm

# Add Relay repository
RUN wget -q "https://cachewerk.s3.amazonaws.com/repos/key.gpg" -O- | apt-key add -
RUN add-apt-repository "deb https://cachewerk.s3.amazonaws.com/repos/deb $(lsb_release -sc) main"
RUN apt-get update

# Install Relay
RUN apt-get install -y \
  php8.1-relay

## If no specific PHP version is installed just omit the version number:

# RUN apt-get install -y \
#  php-dev \
#  php-fpm \
#  php-relay
