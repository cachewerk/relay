FROM --platform=linux/amd64 rockylinux/rockylinux:9

RUN dnf install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-9.noarch.rpm
RUN dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm

RUN dnf update -y

RUN dnf install -y \
  php82-php-cli \
  php82-syspaths

# Add Relay repository
RUN curl -s -o /etc/yum.repos.d/cachewerk.repo "https://repos.r2.relay.so/rpm/el.repo"

# Install Relay
RUN dnf install -y php82-php-relay
