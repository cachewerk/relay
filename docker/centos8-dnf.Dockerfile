FROM centos:8.4.2105

# CentOS Linux 8 is EOL (https://stackoverflow.com/a/70930049)
RUN sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-Linux-*

RUN dnf install -y "https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm"
RUN dnf install -y "https://rpms.remirepo.net/enterprise/remi-release-8.4.rpm"
RUN dnf install -y yum-utils

RUN dnf install -y \
  php80 \
  php80-php-cli \
  php80-php-fpm

ENV PATH="/opt/remi/php80/root/usr/bin/:$PATH"

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://cachewerk.s3.amazonaws.com/repos/rpm/el.repo"

# Install Relay
RUN yum install -y \
  php80-php-relay
