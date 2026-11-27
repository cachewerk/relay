FROM php:8.5-cli

ADD --chmod=0755 https://github.com/mlocati/install-php-extensions/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions relay
