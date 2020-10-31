FROM fusionpbx-docker:latest
LABEL maintainer = AHeavyObject <vongruz@protonmail.com>

# Start as root
USER root

# Add a non-root user to prevent files being created with root permissions on host machine.
ARG PUID=1000
ENV PUID ${PUID}
ARG PGID=1000
ENV PGID ${PGID}

# always run apt update when start and after add new source list, then clean up at end.
RUN set -xe; \
    groupadd -g ${PGID} laradock \
    && useradd -u ${PUID} -g laradock -m laradock \
    && usermod -p "*" laradock -s /bin/bash \
    && usermod -a -G freeswitch laradock \
    && usermod -a -G laradock freeswitch

RUN sed -i "s/user www\-data;/user laradock users;/" /etc/nginx/nginx.conf && \
    sed -i "s/user = www\-data/user = laradock/" /etc/php/7.3/fpm/pool.d/www.conf && \
    sed -i "s/group = www\-data/group = users/" /etc/php/7.3/fpm/pool.d/www.conf && \
    sed -i "s/listen\.owner = www\-data/listen\.owner = laradock/" /etc/php/7.3/fpm/pool.d/www.conf

# Install Required Dependencies
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y curl php-zmq php-xdebug nodejs php-mbstring \
    && apt-get install -y nano mc \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# RUN touch /etc/php/7.3/fpm/php-fpm.conf
RUN LARADOCK_PHP_VERSION=$(php --version | head -1 | awk '{print $2}' | cut -d. -f 1-2) \
    && ln -s /etc/php/${LARADOCK_PHP_VERSION}/mods-available /etc/php/mods-available
#     && echo ${LARADOCK_PHP_VERSION} > a.txt \
#     && service php${LARADOCK_PHP_VERSION}-fpm restart

COPY ./xdebug.ini /etc/php/mods-available/xdebug.ini

# RUN sed -i "s/xdebug.remote_autostart=0/xdebug.remote_autostart=1/" /etc/php/${LARADOCK_PHP_VERSION}/cli/conf.d/xdebug.ini && \
#     sed -i "s/xdebug.remote_enable=0/xdebug.remote_enable=1/" /etc/php/${LARADOCK_PHP_VERSION}/cli/conf.d/xdebug.ini && \
#     sed -i "s/xdebug.cli_color=0/xdebug.cli_color=1/" /etc/php/${LARADOCK_PHP_VERSION}/cli/conf.d/xdebug.ini

# RUN apt-get clean \
#     && rm -rf /var/lib/apt/lists/*

COPY laravel-api /etc/nginx/sites-available/laravel-api
RUN ln -s /etc/nginx/sites-available/laravel-api /etc/nginx/sites-enabled/laravel-api


EXPOSE 444
EXPOSE 9100

# CMD LARADOCK_PHP_VERSION=$(php --version | head -1 | awk '{print $2}' | cut -d. -f 1-2) && service php${LARADOCK_PHP_VERSION}-fpm restart