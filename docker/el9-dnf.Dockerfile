FROM rockylinux/rockylinux:9

RUN dnf install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm
RUN dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm

RUN dnf module reset -y php
RUN dnf module install -y php:remi-8.2

RUN dnf update -y
RUN dnf install -y php-cli

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://repos.r2.relay.so/rpm/el.repo"

# Install Relay
RUN yum install -y php82-php-relay
