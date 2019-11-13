
FROM jpillora/dnsmasq

ARG DNS_HOST_IP=${DNS_HOST_IP:-"127.0.0.1"}
ENV HTTP_USER="admin"
ENV HTTP_PASS="admin-dns-secret"

COPY src/Resources/docker/dev/dns/dnsmasq.conf /etc/dnsmasq.conf
RUN echo "address=/example.dev/${DNS_HOST_IP:-}" >> /etc/dnsmasq.conf
