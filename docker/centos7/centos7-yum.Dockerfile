FROM --platform=linux/amd64 centos:7

RUN yum install -y "https://rpms.remirepo.net/enterprise/remi-release-7.rpm"

RUN yum install -y \
  php81-php-cli \
  php81-syspaths

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://repos.r2.relay.so/rpm/el.repo"

# Install Relay
RUN yum install -y \
  php81-php-relay
