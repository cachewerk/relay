FROM centos:7

RUN yum install -y "https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm"
RUN yum install -y "https://rpms.remirepo.net/enterprise/remi-release-7.rpm"
RUN yum install -y yum-utils

RUN yum-config-manager --disable 'remi-php*' \
  yum-config-manager --enable remi-safe

RUN yum install -y php80 \
  php80-php-cli \
  php80-php-fpm

ENV PATH="/opt/remi/php80/root/usr/bin/:$PATH"

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://cachewerk.s3.amazonaws.com/repos/rpm/el.repo"

# Install Relay
RUN yum install -y \
  php80-php-relay
