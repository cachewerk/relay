FROM litespeedtech/litespeed:6.3.1-lsphp83

RUN apt-get install -y \
  lsb-release \
  gpg

# Add Relay repository
RUN curl -fsSL "https://repos.r2.relay.so/key.gpg" | gpg --dearmor -o /usr/share/keyrings/cachewerk.gpg
RUN echo "deb [signed-by=/usr/share/keyrings/cachewerk.gpg] https://repos.r2.relay.so/deb $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/cachewerk.list > /dev/null
RUN apt-get update -y

# Install Relay
RUN apt-get install -y \
  lsphp83-relay

# Don't start `lswsctrl`
ENTRYPOINT [""]
