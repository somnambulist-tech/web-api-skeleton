# Service Discovery

When running with [data service](https://github.com/dave-redfern/data-service-skeleton) it is
possible to setup service auto-discovery.

[Traefik](https://traefik.io) is being used as a load-balancer and proxy for the main data
service and provided that you follow a few simple steps, any number of micro-services can be
associated with it.

## Automatic Service Discovery

Traefik acts as a proxy and load balancer in a similar way to nginx. It listens on port 80 (or any other)
and provides a gui (usually on 8080, but proxy.example.dev:80 gives access as well). LetsEncrypt can
be setup to provide SSL as well as HTTP auth etc.

To register containers with traefik (called `proxy` in the data project), you need to label the container
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
      - app_example_network
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

__Note:__ you need to ensure that the network is defined as an external type so that your containers
will join the same resources. This network name is defined in the data-service project.
