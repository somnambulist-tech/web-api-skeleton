# Background Tasks with Supervisor

If you need to run cron or scheduled / background tasks, it is best to place these in a
dedicated container that runs supervisor. While you can add supervisor to your standard
app container this does mean that your main app is running multiple different processes
and containerising allows use to isolate these separate concerns.

This is a suggested approach that has it's own trade offs, but does allow for a resilient
supervisor process.

This is a real-world example of adding a supervisor rabbit consumer process using Messenger.

## Docker Configuration

Create a new docker config for the supervisor service. This should be per environment the
same as the other configurations. For example and `src/Resources/docker/dev/supervisor`
folder and then create a new `Dockerfile` that contains:

```dockerfile
FROM somnambulist/php-ppm:7.3-latest
ENV TERM=xterm-256color

RUN apk --update add ca-certificates \
    && apk update \
    && apk upgrade \
    && apk --no-cache add -U \
    php7-pdo_pgsql \
    php7-pgsql \
    php7-pecl-amqp \
    supervisor \
    && rm -rf /var/cache/apk/* /tmp/*

# setup custom PHP ini files
COPY src/Resources/docker/dev/supervisor/conf.d/zz-custom.ini /etc/php7/conf.d/

WORKDIR /app

COPY src/Resources/docker/dev/supervisor/docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod 755 /docker-entrypoint.sh

COPY src/Resources/docker/dev/supervisor/conf.d/supervisord.conf /etc/supervisor/supervisord.conf
COPY src/Resources/docker/dev/supervisor/supervisor.d/* /etc/supervisor/conf.d/

COPY composer.* ./
COPY .env* ./

RUN composer install --no-suggest --no-scripts --quiet \
    && rm -rf /tmp/*

COPY . .

CMD ["/docker-entrypoint.sh"]
```

This uses the php-pm image as a base as it is very small, and adds supervisor to it. The main
step is then to run supervisor instead of php-pm and copy over the config files for supervisor.

The entrypoint file does basic setup:

```shell script
#!/usr/bin/env bash

set -e
cd /app

[[ -d "/app/var" ]] || mkdir -m 0777 "/app/var"
[[ -d "/app/var/cache" ]] || mkdir -m 0777 "/app/var/cache"
[[ -d "/app/var/logs" ]] || mkdir -m 0777 "/app/var/logs"
[[ -d "/app/var/tmp" ]] || mkdir -m 0777 "/app/var/tmp"

# wait for the main app container to run before starting processes
# should avoid running migrations at the same time, or remove this and the migrations
# line to avoid conflicts.
sleep 30

/app/bin/console doctrine:migrations:migrate --no-interaction

sleep 5

/usr/bin/supervisord -c /etc/supervisor/supervisord.conf
```

The `docker-compose.yml` then needs a new service so that supervisor is built with the project:

```yaml
  supervisor:
    build:
      context: .
      dockerfile: src/Resources/docker/dev/supervisor/Dockerfile
    depends_on:
      - app
    networks:
      - backend
    labels:
      traefik.enable: "false"
```

## Supervisor Config

Now add the following supervisor config files to a conf.d folder:

For example: `src/Resources/docker/dev/supervisor/conf.d/supervisord.conf`
```text
[supervisord]
nodaemon=true
loglevel=debug

[include]
files=/etc/supervisor/conf.d/*.conf
```

If you decide to call this main config file something else, don't forget to update the
Dockerfile with the new name / location.

Now we can add the actual task configs. You should add these as one per item to run.
In this example all these are grouped together into a `supervisor.d` folder. The basic
file looks like:

```text
[program:domain-events]
process_name=%(program_name)s_%(process_num)02d
command=php /app/bin/console messenger:consume domain_events --memory-limit=256M
autostart=true
autorestart=true
user=root
numprocs=1
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
```

There are a couple of important points to note about this setup:

 * it is running as root by default, you may wish to run as a different user
 * the process name inherits the program name / proc num
 * only a single process is started
 * the output and error output are sent to stdout/err

The last point means that if you run `docker-compose logs -f <supervisor-container-name>`
you will get all the ouput from the process in the docker logs; however this will only
work when the supervisor loglevel is set to debug (in the previous config file).

The command will run the `messenger:consume` for the `domain_events` queue and run
until the max memory hits 256MB at which point it will die and supervisor will restart it.

## Sync'ing Source Changes

Finally, to keep source files up-to-date in the supervisor container, a SyncIt config is
needed:

```yaml
        supervisor_source_files:
            source: "${PROJECT_DIR}"
            target: "docker://{docker:name=${APP_SERVICE_SUPERVISOR}:name}/app"
            options:
                sync-mode: one-way-replica
            ignore:
                - "composer.*"
```

Additional tasks could be added for composer files etc, otherwise the container will need
rebuilding if dependencies change.
s
