FROM --platform=linux/amd64 centos:8.4.2105

RUN whoami

# CentOS Linux 8 is EOL (https://stackoverflow.com/a/70930049)
RUN sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-Linux-*

RUN dnf install -y "https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm"
RUN dnf install -y "https://rpms.remirepo.net/enterprise/remi-release-8.4.rpm"
RUN dnf config-manager --set-enabled powertools

RUN dnf module reset php -y \
  && dnf module install php:remi-8.0 -y

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://repos.r2.relay.so/rpm/el.repo"

# Install Relay
RUN dnf install -y --nobest php-relay
