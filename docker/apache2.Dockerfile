FROM ubuntu/apache2:2.4-20.04_edge

RUN apt-get update

RUN apt-get install -y \
  libapache2-mod-php

# Add Relay repository
RUN apt-get install -y gnupg wget lsb-release software-properties-common
RUN wget -q "https://cachewerk.s3.amazonaws.com/repos/key.gpg" -O- | apt-key add -
RUN add-apt-repository "deb https://cachewerk.s3.amazonaws.com/repos/deb $(lsb_release -sc) main"
RUN apt-get update

# Install Relay (match the PHP version Apache is using)
RUN apt-get install -y \
  php7.4-relay
