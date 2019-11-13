# Service Discovery

[Traefik](https://traefik.io) is being used to act as a load-balancer and proxy for the
various micro and data services. Traefik requires access to your Docker Host. On a local dev
box this means sharing the `/var/run/docker.sock` file with the container, but on remote
hosts, exposing the remote docker host port either insecure in a highly trusted environment
(port 2375), or over SSL (2376).

The build process will automatically use the local docker socket file unless otherwise
configured.

To fully use the service discovery feature, a local DNS service has been added. This allows
for local host resolution of the *.example.dev domain name.

__Note:__ this project is configured with Traefik 1.X configuration directives and has not
been updated to v2.X yet. This will be done in a future release.

__Note:__ it is suggested to move the base data services into a separate project so that they
can be shared with other micro services.

### Exposed Services

 * http://dns.example.dev:5380/ - DNSMasq console
 * http://proxy.example.dev/ - Traefik Console / Monitoring
 * http://rabbit.example.dev/ - RabbitMQ Management Panel

### Resources

 * https://traefik.io/
 * https://docs.traefik.io/#the-traefik-quickstart-using-docker
 * https://docs.traefik.io/configuration/backends/docker/
 * https://docs.traefik.io/configuration/acme/
 * https://github.com/jpillora/docker-dnsmasq

## Setup Local DNS Resolution

These instructions are for macOS. For other operating systems, Google it or set custom DNS
servers to use your localhost port 1034.

Create a new resolver configuration file:

```shell script
sudo mkdir /etc/resolver
cd /etc/resolver
sudo nano -w example.dev
```

Add the following contents to this file:

```text
domain example.dev
nameserver 127.0.0.1
port 1034
search_order 10
```

Save the changes (Ctrl+O) (oh, not zero) and exit (Ctrl+X).

Check your DNS via `sudo scutil --dns` it should have output similar to:

```text
resolver #8
  domain   : example.dev
  nameserver[0] : 127.0.0.1
  port     : 1034
  flags    : Request A records, Request AAAA records
  reach    : 0x00030002 (Reachable,Local Address,Directly Reachable Address)
  order    : 10

DNS configuration (for scoped queries)

resolver #1
  nameserver[0] : 8.8.8.8
  nameserver[1] : 8.8.4.4
  if_index : 8 (en0)
  flags    : Scoped, Request A records
  reach    : 0x00000002 (Reachable)
```

Finally: make sure any local `/etc/hosts` entries are removed otherwise they will interfere with
the DNS resolution. Either comment them out or delete the lines entirely.

__Note:__ `/etc/resolver` only reloads on _file_ changes, not file _edits_. If you make a mistake
and need to reload the file, `sudo touch tmp` and then `sudo rm tmp` to force a reload.

__Note:__ you may need to clear your dns cache as well: `sudo killall -HUP mDNSResponder`

## Automatic Service Discovery

Traefik acts as a proxy and load balancer in a similar way to nginx. It listens on port 80 (or any other)
and provides a gui (usually on 8080, but proxy.example.dev:80 gives access as well). LetsEncrypt can
be setup to provide SSL as well as HTTP auth etc.

To register containers with traefik (called `proxy` in this project), you need to label the container
with specific tags. Any web service should be labeled with:

 * traefik.port
 * traefik.frontend.rule
 
Any support services e.g. DBs, redis anything without a web-frontend should be tagged with:

 * traefik.enable: "false"

This is required to stop traefik from automatically resolving those services.

For example: to expose the example App API and have traefik route it:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: src/Resources/docker/dev/app/Dockerfile
    networks:
      - backend
    labels:
      traefik.port: 8080
      traefik.frontend.rule: "Host:app.example.dev"
```

The `port` is the INTERNAL container port, the frontend rule is how it should be accessed via
traefik. We could require the previous port by changing the `rule` to: `Host:app.example.dev:4011`

All that is left to do is `dc up -d` and now traefik will pick up the new container and it will
be available immediately.

If you `dc down` services will automatically be removed.

As the traefik config is done through labels, they can be added safely to docker-compose files
without interfering with any other configuration. 
