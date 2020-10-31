- [Fusionpbx API](#fusionpbx-api)
  - [Development environment (docker)](#development-environment-docker)
    - [Setup environment](#setup-environment)
    - [Daily usage](#daily-usage)
  - [Production environment](#production-environment)
    - [Introduction](#introduction)
      - [Install additional packages](#install-additional-packages)
      - [Install API project files](#install-api-project-files)
      - [Update .env file](#update-env-file)
      - [Add nginx virtual host](#add-nginx-virtual-host)
      - [Setup firewall](#setup-firewall)
      - [Get and upload apple VOIP push certificate](#get-and-upload-apple-voip-push-certificate)
      - [Setup and run socket server](#setup-and-run-socket-server)
    - [Check it's working](#check-its-working)
    - [Update](#update)
  - [Documenations](#documenations)


# Fusionpbx API

## Development environment (docker)

For developing we use docker. So it's assumed you have docker installed,
being able to run it without `sudo` (your user is added to `docker` group).

### Setup environment

Run commands in terminal and then follow steps. After done you'll have you `fusionpbx` under https://localhost 
and api accessible under https://localhost:444

```bash
git clone git@github.com:gruz/fusionpbx-api.git
cd fusionbpx-api
bin/init
```

### Daily usage

> Make sure other services are stopped to have corresponding ports opened.

Start docker

`bin/start`

Stop docker

`bin/stop`


## Production environment

> Note, the docs below may be outdated for now. We will update them in the future.

### Introduction

It's assumed you follow FusionPBX installation manual and have your Debian server running.

The API will be accessible at your server under port 444 (you are free to change it)

The main steps would be:

* Install additional packages
* Get the API code and place it to your server
* Update .env file
* Add nginx virtual host
* Setup firewall
* Get and upload apple VOIP push certificate


#### Install additional packages

```bash
apt install composer php-zmq nodejs
```

#### Install API project files

```
# cd /var/www
# git clone git@github.com:gruz/fusionpbx-api.git laravel-api
# cd laraverl-api
# composer update
# cp .env.example .env
# chown -R www-data:www-data laravel-api
```

#### Update .env file

Next manually copy database credentials from `/etc/fusionpbx/config.php` to `/var/www/laravel-api/.env`

```bash
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan migrate
sudo -u www-data php artisan passport:install
```

The latest command will generate key pairs.

Copy-paste the generated secrets and IDs into your .env file like this

```env
PERSONAL_CLIENT_ID=1
PERSONAL_CLIENT_SECRET=mR7k7ITv4f7DJqkwtfEOythkUAsy4GJ622hPkxe6
PASSWORD_CLIENT_ID=2
PASSWORD_CLIENT_SECRET=FJWQRS3PQj6atM6fz5f6AtDboo59toGplcuUYrKL
```

Change `MOTHERSHIP_DOMAIN` domain in .env to your domain.


#### Add nginx virtual host

Edit `/etc/nginx/sites-available/fusionpbx` and add code like this (note port 444 which you can change here and in the firewall section)

```
server {
        listen 444;
        server_name fusionpbx;
        ssl                     on;
        ssl_certificate         /etc/ssl/certs/nginx.crt;
        ssl_certificate_key     /etc/ssl/private/nginx.key;
        ssl_protocols           TLSv1 TLSv1.1 TLSv1.2;
        ssl_ciphers             HIGH:!ADH:!MD5:!aNULL;

        #letsencrypt
        location /.well-known/acme-challenge {
                root /var/www/letsencrypt;
        }

        access_log /var/log/nginx/access.log;
        error_log /var/log/nginx/error.log;

        client_max_body_size 80M;
        client_body_buffer_size 128k;

        location / {
                root /var/www/laravel-api/public;
                index index.php;
                try_files $uri $uri/ /index.php?$query_string;
        }


        location ~ \.php$ {
                fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
                #fastcgi_pass 127.0.0.1:9000;
                fastcgi_index index.php;
                include fastcgi_params;
                fastcgi_param   SCRIPT_FILENAME /var/www/laravel-api/public$fastcgi_script_name;
        }

        # Disable viewing .htaccess & .htpassword & .db
        location ~ .htaccess {
                deny all;
        }
        location ~ .htpassword {
                deny all;
        }
        location ~^.+.(db)$ {
                deny all;
        }
}
```

Restart server

```bash
service nginx restart
```

#### Setup firewall

Allow your port in Firewall

```bash
iptables -A INPUT -p tcp --dport 444 --jump ACCEPT
iptables -A INPUT -p tcp --dport 8080 --jump ACCEPT
```

Now we want to make firewal respect your port setting after reboot.

Save current rules to a file

```bash
iptables-save > /etc/iptables.up.rules
```

Create a boot file

```bash
nano /etc/network/if-pre-up.d/iptables
```

with contents

```bash
#!/bin/sh
/sbin/iptables-restore < /etc/iptables.up.rules
```

Make it executable

```bash
chmod +x /etc/network/if-pre-up.d/iptables
```

#### Get and upload apple VOIP push certificate

This is needed to send push notifications to wakeup Apple devices.
Get the certificate at Apple Developer Portal and place it to **resources/certs/VOIP.pem**

Edit `.env` file and place the path to the cert file as well as password if you have it setup.

Search for `VOIP_APPLE_CERT_PATH` and `VOIP_APPLE_CERT_PASSPHRASE` in your `.env` file.

#### Setup and run socket server

For production site create configuration suppressing output

```bash
cat <<EOF > /etc/supervisor/conf.d/laravel-ratchet.conf
[program:laravel-ratchet]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel-api/artisan ratchet:serve --driver=IoServer -q
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/laravel-api/storage/logs/ratchet.log
EOF
```

For dev site use the same config except the -q key

```bash
cat <<EOF > /etc/supervisor/conf.d/laravel-ratchet.conf
[program:laravel-ratchet]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel-api/artisan ratchet:serve --driver=IoServer
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/laravel-api/storage/logs/ratchet.log
EOF
```

Enable and start

```bash
sudo supervisorctl reread
sudo supervisorctl update
supervisorctl start laravel-ratchet:*
```

### Check it's working

If you open **https://yoursite.com:444** (note HTTPS!) you should see something like

```json
{"title":"FusionPBX API","version":"0.0.1"}
```

### Update

Login to your server via ssh and go to the laravel folder

```bash
cd /var/www/laravel-api/
```

Switch to `www-data` user
```bash
su -m -l www-data
```

> Don't care when you get `-su: /root/.bash_profile: Permission denied`. Just ignore.


```bash
git pull
```

Install composer packaged to get new added packages and remove unneeded ones

```bash
composer install
```

Run laravel migration

```bash
php artisan migrate
```

Check `.env.example` file for new entries (compare it with your current file). If there are new lines at the bottom, then update your `.env` file with the new files.

Check if your certificates (like VOIP push cert) are in place.

## Documenations

Check this repository wiki