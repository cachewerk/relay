FROM --platform=linux/amd64 rockylinux:8.6

RUN dnf install -y "https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm"
RUN dnf install -y "https://rpms.remirepo.net/enterprise/remi-release-8.6.rpm"
RUN dnf install -y yum-utils

RUN dnf install -y \
  php80 \
  php80-php-cli \
  php80-php-fpm

ENV PATH="/opt/remi/php80/root/usr/bin/:$PATH"

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://repos.r2.relay.so/rpm/el.repo"

# Install Relay
RUN yum install -y \
  php80-php-relay
