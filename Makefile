repoversion=$(shell LANG=C aptitude show php-ease-fluentpdo | grep Version: | awk '{print $$2}')
nextversion=$(shell echo $(repoversion) | perl -ne 'chomp; print join(".", splice(@{[split/\./,$$_]}, 0, -1), map {++$$_} pop @{[split/\./,$$_]}), "\n";')

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


release:
	echo Release v$(nextversion)
	dch -v $(nextversion) `git log -1 --pretty=%B | head -n 1`
	debuild -i -us -uc -b
	git commit -a -m "Release v$(nextversion)"
	git tag -a $(nextversion) -m "version $(nextversion)"

