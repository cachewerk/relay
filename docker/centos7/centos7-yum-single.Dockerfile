FROM --platform=linux/amd64 centos:7

RUN yum install -y "https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm"
RUN yum install -y "https://rpms.remirepo.net/enterprise/remi-release-7.rpm"
RUN yum install -y yum-utils

RUN yum-config-manager --disable 'remi*' \
  && yum-config-manager --enable remi-php74

RUN yum install -y \
  php-cli

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://repos.r2.relay.so/rpm/el.repo"

# Install Relay
RUN yum install -y \
  php-relay
