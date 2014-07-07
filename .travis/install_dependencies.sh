#!/usr/bin/env bash

# Install everything
sudo apt-get install -y --force-yes apache2 libapache2-mod-php5 php5-mysql php5-sqlite

# Configure Apache
WEBROOT="$(pwd)"
CGIROOT=`dirname "$(which php-cgi)"`
echo "WEBROOT: $WEBROOT"
echo "CGIROOT: $CGIROOT"
sudo echo "<VirtualHost *:80>
        DocumentRoot $WEBROOT
        <Directory />
                Options FollowSymLinks
                AllowOverride All
        </Directory>
        <Directory $WEBROOT >
                Options Indexes FollowSymLinks MultiViews
                AllowOverride All
                Order allow,deny
                allow from all
        </Directory>

		# Configure PHP as CGI
		ScriptAlias /local-bin $CGIROOT
		DirectoryIndex index.php index.html
		AddType application/x-httpd-php5 .php
		Action application/x-httpd-php5 '/local-bin/php-cgi'

</VirtualHost>" | sudo tee /etc/apache2/sites-available/default > /dev/null
cat /etc/apache2/sites-available/default

sudo a2enmod rewrite
sudo a2enmod actions
sudo service apache2 restart
sudo chmod -R 777 ./app/storage/

# mysql -e 'CREATE DATABASE dlapi;'
# sed s/%database_name%/myapp_test/ app/config/parameters.ini-dist | sed s/%database_login%/root/ | sed s/%database_password%// > app/config/parameters.ini

# Configure custom domain
echo "127.0.0.1 dl-api.dev" | sudo tee --append /etc/hosts

echo "TRAVIS_PHP_VERSION: $TRAVIS_PHP_VERSION"
