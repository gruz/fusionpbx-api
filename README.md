- [Fusionpbx API](#fusionpbx-api)
  - [Installation](#installation)
    - [Prepare FusionPBX installation](#prepare-fusionpbx-installation)
      - [Allow remote connection to postgres DB](#allow-remote-connection-to-postgres-db)
      - [Enable remote `reload XML` and `flush cache` for FusionPBX](#enable-remote-reload-xml-and-flush-cache-for-fusionpbx)
        - [Run by SSH from Laravel API server](#run-by-ssh-from-laravel-api-server)
        - [Run as a CURL request protected by hash](#run-as-a-curl-request-protected-by-hash)
    - [Install laravel API](#install-laravel-api)
  - [Documenation](#documenation)
  - [Testing](#testing)
    - [Prepare test database](#prepare-test-database)
    - [Git hook for pre-push](#git-hook-for-pre-push)
    - [Working with xDebug at VirtualBox](#working-with-xdebug-at-virtualbox)


# Fusionpbx API

> It's a very early development stage for a FusionPBX API using Laravel.

## Installation

### Prepare FusionPBX installation

It's assumed you have a running FusionPBX server.

The Laravel API needs to connect to FusionPBX postgres DB and run a hook (see below) to reload XML and flush cache remotely.

If you place the Laravel API server for FusionPBX as a second virtual host at the same server,
then you can connect to DB and run the hook (see below) directly at the server.

If you decide to place the Laravel API at another server, you must do the following:

* Allow remote connection to postgres DB
* Enable remote `reload XML` and `flush cache` for FusionPBX

#### Allow remote connection to postgres DB

Login into FusionPBX server and unblock your IP in Postgres settings and Firewall.

A shortcut to do this

```bash
ssh root@192.168.0.160 'bash -s' < bin/allow_pg  192.168.0.101
```

where `192.168.0.160` if your FusionPBX server and `192.168.0.101` is your Laravel API server IP

#### Enable remote `reload XML` and `flush cache` for FusionPBX

The hook is just a PHP file found in [bin/fpbx_hook.php](bin/fpbx_hook.php)

You must place it at the FusionPBX server then you have 2 options:

* Run by SSH from Laravel API server
* Run as a CURL request protected by hash

The command setup here will be needed for Laravel API configuration.

##### Run by SSH from Laravel API server

Let's assume you place the hook into `/var/www/hook/fpbx_hook.php` at 
your FusionPBX server. And you have FusionPBX placed in `/var/www/fusionpbx`.

Setup passwordless ssh access to be able to run from your Laravel API server a command like

```bash
ssh -t root@192.168.0.160 sudo -u www-data php /var/www/hook/fpbx_hook.php /var/www/fusionpbx
```

where `192.168.0.160` is your FusionPBX server.

The response should be like this:

```bash
+OK cache flushed
+OK [Success]

Connection to 192.168.0.160 closed.
```

If it works, don't forget to add the following line to your laravel .env file:

```
FPBX_HOOK='ssh -t root@192.168.0.160 sudo -u www-data php /var/www/hook/fpbx_hook.php /var/www/fusionpbx'
```

##### Run as a CURL request protected by hash

The idea is to provide an URL to run the hook. Something like 
`https://192.168.0.160:445/fpbx_hook.php?hash=464ab3451cf0ccdeda1f0b61300639498b6ebca06e3e8da6d6974b5540a634de`


For example you place the hook file in `/var/www/hook/fpbx_hook.php` at you FusionPBX server.

Edit it and change the hash at the top of the file to something unique to protect the url.

Add nging config like this `/etc/nginx/sites-available/hook`

```
server {
        listen 445;
        server_name fpbx_hook;
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
                root /var/www/fpbx-hook;
                index index.php;
                try_files $uri $uri/ /index.php?$query_string;
        }


        location ~ \.php$ {
                fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
                #fastcgi_pass 127.0.0.1:9000;
                fastcgi_index index.php;
                include fastcgi_params;
                fastcgi_param   SCRIPT_FILENAME /var/www/fpbx-hook$fastcgi_script_name;
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

Create a symlink to enable the config and restart nginx and fpm

```bash
ln -s /etc/nginx/sites-available/hook /etc/nginx/sites-enabled/hook
PHP_VERSION=$(php --version | head -1 | awk '{print $2}' | cut -d. -f 1-2)
/etc/init.d/php${PHP_VERSION}-fpm restart
/etc/init.d/nginx restart
```

So if you open the link above with the correct hash it should show something like

```
+OK cache flushed +OK [Success] 
```

If it works, don't forget to add the following line to your laravel .env file:

```
FPBX_HOOK='curl -k GET https://192.168.0.160:445/fpbx_hook.php?hash=464ab3451cf0ccdeda1f0b61300639498b6ebca06e3e8da6d6974b5540a634de'
```

### Install laravel API

```bash
git clone git@github.com:gruz/fusionpbx-api.git
cd fusionpbx-api
cp .env-example .env
```

Update .env file with you data.


Next if you want to start regular laravel server:

```bash
composer install
php artisan serve
```

Of if you want to start a docker with xdebug, mailhog

```bash
git submodule update --init --recursive
bin/start dev
bin/composer install
```

To login into docker you can use

* bin/login
* bin/login_root

To run composer and artisan without docker login

* bin/composer
* bin/artisan


## Documenation

API Swagger documentation should be available under as json file [api-docs.json](storage/api-docs/api-docs.json)

Or try it in online [swagger editor](https://editor.swagger.io/?url=https://raw.githubusercontent.com/gruz/fusionpbx-api/master/storage/api-docs/api-docs.json)

When running the project the docs are available under:
* yourdomain.com/docs/api
* 

## Testing

### Prepare test database

The command below will create a copy of the `fusionpbx` database named `fusionpbx_test` used for testing.

```bash
php artisan db:maketest
```

### Git hook for pre-push

To automatically run tests before pushing use such a hook.

Create file `.git/hooks/pre-push` with contents

```
#!/bin/bash

branch=`git rev-parse --abbrev-ref HEAD`
echo "Running tests before pushing ...."
if [ $branch == 'master' ] || [ $branch == 'dev' ]; then
  # exit_code=$(ssh -t root@192.168.0.160 "cd /var/www/laravel-api; php artisan test" > /dev/null 2>/dev/null )$?
  ssh -t root@192.168.0.160 "cd /var/www/laravel-api; php artisan db:maketest; php artisan test"
  LAST=$?
  if [ $LAST -gt 0 ]
    then echo "Did not push because of failing tests"
  fi
  exit $LAST
fi

exit 0
```

Note `192.168.0.160` - this is the IP of the virtualbox. Root SSH must access must be enabled. https://blog.eldernode.com/enable-root-login-via-ssh-in-debian/


### Working with xDebug at VirtualBox

To run tests from VirtualBox with xdebug enable login into it and run a laravel test shortcut which uses host IP to call back

```
cd /var/www/fusionpbx-api/laravel-api
bin/test
```

Or a certain class
```
cd /var/www/fusionpbx-api/laravel-api
bin/test --filter TestClassName
```

