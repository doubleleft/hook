# This Makefile downloads and installs hook dependencies
#
# It depends on GNU Make.
# Tested on Linux (Ubuntu, Gentoo) and Mac OSX.
#
# The default target is "make install", but it also provides "make test" and "make docs".

SHELL := /bin/bash
APIGEN_PATH = ~/Downloads/apigen
CURPATH := $(shell pwd -P)
export PATH=$(HOME)/bin:$(shell echo $$PATH)

default: install

install:
	# check dependencies
ifneq ($(shell which php > /dev/null 2>&1; echo $$?),0)
	$(error "Missing php-cli.")
endif

	# install composer if we don't have it already
ifneq ($(shell which composer > /dev/null 2>&1 || test -x $(HOME)/bin/composer; echo $$?),0)
	mkdir -p $(HOME)/bin
	curl -sS https://getcomposer.org/installer | php -d detect_unicode=Off -- --install-dir=$(HOME)/bin --filename=composer
	chmod +x $(HOME)/bin/composer
endif

	composer install --prefer-dist

	@echo "Finished"

test:
	./vendor/bin/phpunit --configuration ./tests/phpunit.xml
	# DB_DRIVER=mysql ./vendor/bin/phpunit --configuration ./tests/phpunit.xml
	# DB_DRIVER=postgres ./vendor/bin/phpunit --configuration ./tests/phpunit.xml
	# DB_DRIVER=sqlite ./vendor/bin/phpunit --configuration ./tests/phpunit.xml
	# DB_DRIVER=mongodb ./vendor/bin/phpunit --configuration ./tests/phpunit.xml
	# DB_DRIVER=sqlsrv ./vendor/bin/phpunit --configuration ./tests/phpunit.xml

docs:
	mkdir -p ../dl-api-docs/
	php -d memory_limit=512M ${APIGEN_PATH}/apigen.php --destination ../dl-api-docs/ \
																--exclude */tests/* \
																--exclude */Tests/* \
																--source ./src/ \
																--source ./vendor/guzzle/guzzle/src \
																--source ./vendor/illuminate \
																--source ./vendor/doctrine \
																--source ./vendor/slim/slim/Slim \
																--source ./vendor/symfony
	open ../dl-api-docs/index.html
