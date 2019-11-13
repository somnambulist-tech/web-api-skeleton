
#
# Reverse Proxy for web services
#

FROM traefik:alpine

ARG PROXY_DOCKER_HOST=${PROXY_DOCKER_HOST:-unix:///var/run/docker.sock}

COPY src/Resources/docker/dev/proxy/traefik.toml /etc/traefik/traefik.toml
COPY src/Resources/docker/dev/proxy/certs/* /etc/traefik/certs/

RUN echo "endpoint = \"${PROXY_DOCKER_HOST}\"" >> /etc/traefik/traefik.toml
