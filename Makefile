# This Makefile downloads and installs dl-api dependencies and the dl-api commandline.
# It depends on GNU Make.
# For dl-api installation we need php-cli, php-json and npm.
# It has been tested on Linux (Ubuntu, Gentoo) and Mac OSX.
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

ifneq ($(shell which npm > /dev/null 2>&1 > /dev/null; echo $$?),0)
	$(error "Missing npm.")
endif

	# install composer if we don't have it already
ifneq ($(shell which composer > /dev/null 2>&1 || test -x $(HOME)/bin/composer; echo $$?),0)
	mkdir -p $(HOME)/bin
	curl -sS https://getcomposer.org/installer | php -d detect_unicode=Off -- --install-dir=$(HOME)/bin --filename=composer
	chmod +x $(HOME)/bin/composer
endif

	# ./api
	cd $(CURPATH)/api && composer install

	# ./commandline
	mkdir -p $(HOME)/bin
	cd "$(CURPATH)/commandline" && composer install
	ln -sf "$(CURPATH)/commandline/bin/dl-api" "$(HOME)/bin/dl-api"
	chmod +x "$(CURPATH)/commandline/bin/dl-api" "$(HOME)/bin/dl-api"
	npm --prefix "$(CURPATH)/commandline/console" install "$(CURPATH)/commandline/console"

	# add bash_completion
ifneq ($(shell grep -qs "commandline/bash_completion" $(HOME)/.{profile,bash{rc,_profile}}; echo $$?),0)
ifeq ($(shell test -f $(HOME)/.bash_profile),0)
	echo "source $(CURPATH)/commandline/bash_completion" >> $(HOME)/.bash_profile
else
	echo "source $(CURPATH)/commandline/bash_completion" >> $(HOME)/.profile
endif
endif

	# add ~/bin to user PATH
ifneq ($(shell grep -qs "\(~\|\$${\?HOME}\?\)/bin" $(HOME)/.{profile,bash{rc,_profile}}; echo $$?),0)
ifeq ($(shell test -f $(HOME)/.bash_profile),0)
	echo "export PATH=~/bin:\$$PATH" >> $(HOME)/.bash_profile
else
	echo "export PATH=~/bin:\$$PATH" >> $(HOME)/.profile
endif
endif

	@echo "Finished"

test:
	./api/vendor/bin/phpunit --configuration ./api/tests/phpunit.xml

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

