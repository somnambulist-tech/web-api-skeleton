
FROM somnambulist/php-ppm:8.3-latest
ENV TERM=xterm-256color

RUN apk --update add ca-certificates \
    && apk update \
    && apk upgrade \
    && apk --no-cache add -U \
    php83-pdo_pgsql \
    php83-pgsql \
    php83-pecl-amqp \
    && rm -rf /var/cache/apk/* /tmp/*

# setup custom PHP ini files
COPY config/docker/prod/app/conf.d/zz-custom.ini /etc/php83/conf.d/

# app will be mounted here, so set this as our working directory
WORKDIR /app

COPY config/docker/prod/app/docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod 755 /docker-entrypoint.sh
RUN composer selfupdate

COPY ppm*.json ./
COPY composer.* ./
COPY .env* ./

RUN composer install --no-scripts --quiet -o && rm -rf /tmp/*

COPY . .

EXPOSE 8080

# run php-pm on the custom ip so it is externally available
CMD [ "/docker-entrypoint.sh", "start", "--cgi-path=/usr/bin/php-cgi83", "--host=0.0.0.0" ]
