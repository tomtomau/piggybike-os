release:
	php app/console cache:clear --env=prod && php app/console cache:clear --env=dev --no-debug
	chmod -R 777 app/logs app/cache
