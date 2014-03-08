SCRIPTPATH=`pwd -P`

default:
	composer.phar install
	ln -sF "$(SCRIPTPATH)/bin/dl-api" /bin/dl-api
	chmod +x "$(SCRIPTPATH)/bin/dl-api" /bin/dl-api
	npm --prefix ./console install ./console
	echo "Finished\n"
