FROM ubuntu:18.04

RUN apt update
RUN apt upgrade -y

RUN apt install -y \
  curl \
  ca-certificates \
  apt-transport-https \
  software-properties-common

RUN add-apt-repository ppa:ondrej/php

ARG DEBIAN_FRONTEND=noninteractive

RUN apt install -y \
  php8.1-dev \
  php8.1-fpm

RUN curl -s https://cachewerk.s3.amazonaws.com/repos/key.gpg | apt-key add -
RUN add-apt-repository "deb https://cachewerk.s3.amazonaws.com/repos/deb $(lsb_release -cs) main"
RUN apt install -y php8.1-relay

## This works as well (if no specific PHP version is installed)
# RUN apt install -y php-dev php-fpm
# RUN apt install -y php-relay
