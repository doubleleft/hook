#!/bin/sh
APIGEN_PATH = ~/Downloads/apigen
CURPATH=`pwd -P`

default:
	# TODO: install composer automatically, if it isn't instaled
	# if [ !-z `which composer` ]; then
	# 	curl -sS https://getcomposer.org/installer | php -d detect_unicode=Off -- --install-dir=/usr/local/bin --filename=composer
	# fi

	# ./api
	cd "$(CURPATH)/api" composer install
	mkdir -p "$(CURPATH)/api/app/storage"
	chmod -R 777 "$(CURPATH)/api/app/storage"

	# ./commandline
	cd "$(CURPATH)/commandline" && composer install
	ln -sF "$(CURPATH)/commandline/bin/dl-api" "/bin/dl-api"
	chmod +x "$(CURPATH)/commandline/bin/dl-api" "/bin/dl-api"
	npm --prefix "$(CURPATH)/commandline/console" install "$(CURPATH)/commandline/console"
	echo "\nsource $(CURPATH)/commandline/bash_completion\n" >> ~/.bash_profile
	echo "Finished"

publish-docs:
	php ${APIGEN_PATH}/apigen.php --source api/app/ --destination ./docs
