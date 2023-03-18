FROM rockylinux/rockylinux:9

ARG DEBIAN_FRONTEND=noninteractive

RUN dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm
RUN dnf install https://rpms.remirepo.net/enterprise/remi-release-9.rpm

RUN dnf module reset php
RUN dnf module install php:remi-8.2

RUN dnf update
RUN dnf install php-cli

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://repos.r2.relay.so/rpm/el.repo"

# Install Relay
RUN dnf install php82-php-relay
