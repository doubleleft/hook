APIGEN_PATH = ~/Downloads/apigen

publish-docs:
	php ${APIGEN_PATH}/apigen.php --source api/app/ --destination ./docs
