all:

prepare:
	rm -rf ./tests/test.sqlite
	touch ./tests/test.sqlite
	vendor/bin/phinx migrate -c Examples/phinx-adapter.php
	vendor/bin/phinx seed:run -c Examples/phinx-adapter.php


deb:
	debuild -us -uc

phpunit: prepare
	vendor/bin/phpunit --bootstrap tests/bootstrap.php --configuration phpunit.xml

