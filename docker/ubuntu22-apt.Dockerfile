FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update
RUN apt-get install -y curl software-properties-common

RUN add-apt-repository ppa:ondrej/php

RUN apt-get install -y \
  php8.1-fpm

# Add Relay repository
RUN curl -fsSL "https://repos.r2.relay.so/key.gpg" | gpg --dearmor -o /usr/share/keyrings/cachewerk.gpg
RUN echo "deb [signed-by=/usr/share/keyrings/cachewerk.gpg] https://repos.r2.relay.so/deb $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/cachewerk.list > /dev/null
RUN apt-get update

# Install Relay
RUN apt-get install -y \
  php8.1-relay

## If no specific PHP version is installed just omit the version number:

# RUN apt-get install -y \
#  php-fpm \
#  php-relay
