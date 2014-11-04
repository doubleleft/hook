#!/usr/bin/env bash
export WORKING_DIR=`pwd`

# server stack
sudo apt-get update -y -q
sudo apt-get install nginx

# enable php-fpm
echo "cgi.fix_pathinfo = 0;" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

# configure nginx
sudo sed -i -e"s/user www-data;/user root;/" /etc/nginx/nginx.conf
sudo sed -i -e"s/keepalive_timeout\s*65/keepalive_timeout 2/" /etc/nginx/nginx.conf
sudo sed -i -e"s/keepalive_timeout 2/keepalive_timeout 2;\n\tclient_max_body_size 3m/" /etc/nginx/nginx.conf
cat ./.travis/nginx.conf | sed -e "s,%TRAVIS_BUILD_DIR%,$WORKING_DIR/public,g" | sudo tee /etc/nginx/sites-available/default > /dev/null
sudo service nginx restart

# Print nginx configuration
cat /etc/nginx/sites-available/default

echo "php-fpm config:"
cat ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf

# apply server permissions
sudo chown -R www-data $WORKING_DIR/shared
sudo chown -R www-data $WORKING_DIR/public/storage

# Configure custom domain
echo "127.0.0.1 hook.dev" | sudo tee --append /etc/hosts

# output trying to create an app to check if API is responding
curl -XPOST http://hook.dev/public/index.php/apps --data '{"app":{"name":"testing"}}'

# then create default app
curl -XPOST http://hook.dev/public/index.php/apps --data '{"app":{"name":"travis"}}' > tests/app.json
cat tests/app.json

echo "nginx logs:"
cat $WORKING_DIR/shared/logs.txt
