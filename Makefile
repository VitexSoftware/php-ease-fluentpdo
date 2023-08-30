all:

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

packages:
	debuild -us -uc
