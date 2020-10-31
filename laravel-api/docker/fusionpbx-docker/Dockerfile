FROM debian:buster
LABEL maintainer = Igor Olhovskiy <IgorOlhovskiy@gmail.com>

# Install Required Dependencies
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get install -y \
        ca-certificates \
        git \
        ssl-cert \
        ghostscript \
        libtiff5-dev \
        libtiff-tools \
        nginx \
        php \
        php-cli \
        php-fpm \
        php-pgsql \
        php-odbc \
        php-curl \
        php-imap \
        php-xml \
        wget \
        curl \
        openssh-server \
        supervisor \
        net-tools \
        gnupg2 \
        netcat \
    && PHP_VERSION=$(php --version | head -1 | awk '{print $2}' | cut -d. -f 1-2) \
     && wget https://raw.githubusercontent.com/samael33/fusionpbx-install.sh/master/debian/resources/nginx/fusionpbx -O /etc/nginx/sites-available/fusionpbx \
    && find /etc/nginx/sites-available/fusionpbx -type f -exec sed -i 's/\/var\/run\/php\/php7.1-fpm.sock/\/run\/php\/php'"$PHP_VERSION"'-fpm.sock/g' {} \; \
    && ln -s /etc/nginx/sites-available/fusionpbx /etc/nginx/sites-enabled/fusionpbx \
    && ln -s /etc/ssl/private/ssl-cert-snakeoil.key /etc/ssl/private/nginx.key \
    && ln -s /etc/ssl/certs/ssl-cert-snakeoil.pem /etc/ssl/certs/nginx.crt \
    && rm /etc/nginx/sites-enabled/default \
    && wget -O - https://files.freeswitch.org/repo/deb/debian-release/fsstretch-archive-keyring.asc | apt-key add - \
    && echo "deb http://files.freeswitch.org/repo/deb/debian-release/ buster main" > /etc/apt/sources.list.d/freeswitch.list \
    && apt-get update \
    && apt-get install -y \
        freeswitch-meta-bare \
        freeswitch-conf-vanilla \
        freeswitch-mod-commands \
        freeswitch-mod-console \
        freeswitch-mod-logfile \
        freeswitch-lang-en \
        freeswitch-mod-say-en \
        freeswitch-sounds-en-us-callie \
        freeswitch-mod-enum \
        freeswitch-mod-cdr-csv \
        freeswitch-mod-event-socket \
        freeswitch-mod-sofia \
        freeswitch-mod-loopback \
        freeswitch-mod-conference \
        freeswitch-mod-db \
        freeswitch-mod-dptools \
        freeswitch-mod-expr \
        freeswitch-mod-fifo \
        freeswitch-mod-httapi \
        freeswitch-mod-hash \
        freeswitch-mod-esl \
        freeswitch-mod-esf \
        freeswitch-mod-fsv \
        freeswitch-mod-valet-parking \
        freeswitch-mod-dialplan-xml \
        freeswitch-mod-sndfile \
        freeswitch-mod-native-file \
        freeswitch-mod-local-stream \
        freeswitch-mod-tone-stream \
        freeswitch-mod-lua \
        freeswitch-meta-mod-say \
        freeswitch-mod-xml-cdr \
        freeswitch-mod-verto \
        freeswitch-mod-callcenter \
        freeswitch-mod-rtc \
        freeswitch-mod-png \
        freeswitch-mod-json-cdr \
        freeswitch-mod-shout \
        freeswitch-mod-sms \
        freeswitch-mod-sms-dbg \
        freeswitch-mod-cidlookup \
        freeswitch-mod-memcache \
        freeswitch-mod-imagick \
        freeswitch-mod-tts-commandline \
        freeswitch-mod-directory \
        freeswitch-mod-flite \
        freeswitch-mod-distributor \
        freeswitch-meta-codecs \
        freeswitch-mod-pgsql \
        freeswitch-music-default \
    && usermod -a -G freeswitch www-data \
    && usermod -a -G www-data freeswitch \
    && chown -R freeswitch:freeswitch /var/lib/freeswitch \
    && chmod -R ug+rw /var/lib/freeswitch \
    && find /var/lib/freeswitch -type d -exec chmod 2770 {} \; \
    && mkdir /usr/share/freeswitch/scripts \
    && chown -R freeswitch:freeswitch /usr/share/freeswitch \
    && chmod -R ug+rw /usr/share/freeswitch \
    && find /usr/share/freeswitch -type d -exec chmod 2770 {} \; \
    && chown -R freeswitch:freeswitch /etc/freeswitch \
    && chmod -R ug+rw /etc/freeswitch \
    && mkdir -p /etc/fusionpbx \
    && chmod 777 /etc/fusionpbx \
    && find /etc/freeswitch -type d -exec chmod 2770 {} \; \
    && chown -R freeswitch:freeswitch /var/log/freeswitch \
    && chmod -R ug+rw /var/log/freeswitch \
    && find /var/log/freeswitch -type d -exec chmod 2770 {} \; \
    && find /etc/freeswitch/autoload_configs/event_socket.conf.xml -type f -exec sed -i 's/::/127.0.0.1/g' {} \; \
    && mkdir -p /run/php/ \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

ADD ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN PHP_VERSION=$(php --version | head -1 | awk '{print $2}' | cut -d. -f 1-2) \
    && find /etc/supervisor/conf.d/supervisord.conf -type f -exec sed -i 's/php-fpm7.3/php-fpm'"$PHP_VERSION"'/g' {} \; \
    && find /etc/supervisor/conf.d/supervisord.conf -type f -exec sed -i 's/\/php\/7.3\//\/php\/'"$PHP_VERSION"'\//g' {} \;
COPY start-freeswitch.sh /usr/bin/start-freeswitch.sh

EXPOSE 80
EXPOSE 443
EXPOSE 5060/udp
VOLUME ["/etc/freeswitch", "/var/lib/freeswitch", "/usr/share/freeswitch", "/etc/fusionpbx", "/var/www/fusionpbx"]

CMD /usr/bin/supervisord -n
