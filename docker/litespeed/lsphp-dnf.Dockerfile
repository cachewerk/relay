FROM --platform=linux/amd64 rockylinux/rockylinux:9

# Set up LSPHP
RUN curl -L https://repo.litespeed.sh | bash

# Install LSPHP
RUN dnf install -y \
  lsphp83

# Install Relay dependencies
RUN dnf install -y \
  lsphp83-pecl-igbinary \
  lsphp83-pecl-msgpack

# Add Relay repository
RUN curl -s -o "/etc/yum.repos.d/cachewerk.repo" "https://repos.r2.relay.so/rpm/el.repo"

# Install Relay
RUN yum install -y \
  lsphp83-relay

# FIX: Only need in Relay CI
RUN ln -s /usr/local/lsws/lsphp83/bin/php /usr/local/bin/php
