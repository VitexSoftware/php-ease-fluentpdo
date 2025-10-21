# vim: set tabstop=8 softtabstop=8 noexpandtab:
.PHONY: help
help: ## Displays this list of targets with descriptions
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}'

.PHONY: static-code-analysis
static-code-analysis: vendor ## Runs a static code analysis with phpstan/phpstan
	vendor/bin/phpstan analyse --configuration=phpstan-default.neon.dist --memory-limit=-1

.PHONY: static-code-analysis-baseline
static-code-analysis-baseline: check-symfony vendor ## Generates a baseline for static code analysis with phpstan/phpstan
	vendor/bin/phpstan analyze --configuration=phpstan-default.neon.dist --generate-baseline=phpstan-default-baseline.neon --memory-limit=-1

.PHONY: tests
tests: vendor
	vendor/bin/phpunit tests

.PHONY: vendor
vendor: composer.json composer.lock ## Installs composer dependencies
	composer install

.PHONY: cs
cs: ## Update Coding Standards
	vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --diff --verbose

.PHONY: docs
docs: ## Generate documentation using phpDocumentor
	mkdir -p docs
	rm -rf docs/*
	phpdoc -d src --target=docs --defaultpackagename=EaseFluentPDO --title="Ease FluentPDO Documentation"


composer:
	composer install

migration:
	cd Examples ; ../vendor/bin/phinx migrate -c ./phinx-adapter.php ; cd ..

seed:
	cd Examples ; ../vendor/bin/phinx seed:run -c ./phinx-adapter.php ; cd ..

demodata:
	./vendor/bin/phinx seed:run -c ../phinx-adapter.php ; cd ..

newmigration:
	read -p "Enter CamelCase migration name : " migname ; cd Examples ;  ../vendor/bin/phinx create $$migname -c ./phinx-adapter.php ; cd ..

newseed:
	read -p "Enter CamelCase seed name : " migname ; cd Examples ; ./vendor/bin/phinx seed:create $$migname -c ./phinx-adapter.php  ; cd ..

phpunit: composer migration seed
	./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php --configuration ./phpunit.xml

autoload:
	composer update

packages: docs
	debuild -us -uc
