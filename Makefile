SHELL := /bin/bash
APIGEN_PATH = ~/Downloads/apigen
CURPATH := $(shell pwd -P)
export PATH := $(HOME)/bin:$(PATH)
install_composer =

default:

ifneq ($(shell which php 1>&2 > /dev/null; echo $$?),0)
	$(error "Missing php-cli.")
endif	

ifneq ($(shell which npm 1>&2 > /dev/null; echo $$?),0)
	$(error "Missing npm.")
endif

ifneq ($(shell which composer 1>&2 > /dev/null; echo $$? || test -x $(HOME)/bin/composer 1>&2 > /dev/null; echo $$?),0) 
	mkdir -p ~/bin
	-curl -sS https://getcomposer.org/installer | php -d detect_unicode=Off -- --install-dir=$(HOME)/bin --filename=composer
	chmod +x $(HOME)/bin/composer
endif

	# ./api
	cd "$(CURPATH)/api" ~/bin/composer install
	mkdir -p "$(CURPATH)/api/app/storage"
	# chmod -R 755 "$(CURPATH)/api/app/storage"

	# ./commandline
	ln -sf "$(CURPATH)/commandline/bin/dl-api" "$(HOME)/bin/dl-api"
	chmod +x "$(CURPATH)/commandline/bin/dl-api" "$(HOME)/bin/dl-api"
	npm --prefix "$(CURPATH)/commandline/console" install "$(CURPATH)/commandline/console"
	if grep -q "commandline/bash_completion" $(HOME)/.bash_profile
		echo "\nsource $(CURPATH)/commandline/bash_completion\n" >> $(HOME)/.bash_profile
	fi
	echo "Finished"

test:
	./api/vendor/bin/phpunit --configuration ./api/phpunit.xml

docs:
	mkdir -p ../dl-api-docs/
	php -d memory_limit=512M ${APIGEN_PATH}/apigen.php --destination ../dl-api-docs/ \
																--exclude */tests/* \
																--exclude */Tests/* \
																--source ./api/app/ \
																--source ./api/vendor/guzzle/guzzle/src \
																--source ./api/vendor/illuminate \
																--source ./api/vendor/doctrine \
																--source ./api/vendor/slim/slim/Slim \
																--source ./api/vendor/symfony
	open ../dl-api-docs/index.html

