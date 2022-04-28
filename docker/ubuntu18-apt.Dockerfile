FROM ubuntu:18.04

RUN apt-get update
RUN apt-get upgrade -y

RUN apt-get install -y \
  curl \
  ca-certificates \
  apt-transport-https \
  software-properties-common

RUN add-apt-repository ppa:ondrej/php

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get install -y \
  php8.1-dev \
  php8.1-fpm

RUN curl -s https://cachewerk.s3.amazonaws.com/repos/key.gpg | apt-key add -
RUN add-apt-repository "deb https://cachewerk.s3.amazonaws.com/repos/deb $(lsb_release -cs) main"
RUN apt-get install -y \
  php8.1-relay

## If no specific PHP version is installed just omit the version number:

# RUN apt-get install -y \
#  php-dev \
#  php-fpm \
#  php-relay
