FROM --platform=linux/amd64 centos:7

# It's dead, Jim
RUN sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-* && \
    sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-*

RUN yum update -y

RUN yum install -y "https://rpms.remirepo.net/enterprise/remi-release-7.rpm"

RUN yum install -y \
  php81-php-cli \
  php81-syspaths

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://repos.r2.relay.so/rpm/el.repo"

# Install Relay
RUN yum install -y \
  php81-php-relay
