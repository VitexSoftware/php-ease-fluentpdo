SQL Support for EasePHP Framework using FluentPDO
=================================================

![Project logo](php-ease-fluentpdo.svg?raw=true)

[![Latest Stable Version](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/version)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![Total Downloads](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/downloads)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![Latest Unstable Version](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/v/unstable)](//packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![License](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/license)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![Monthly Downloads](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/d/monthly)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)
[![Daily Downloads](https://poser.pugx.org/vitexsoftware/ease-fluentpdo/d/daily)](https://packagist.org/packages/vitexsoftware/ease-fluentpdo)


Installation
============

Download https://github.com/VitexSoftware/php-ease-fluentpdo/archive/master.zip or use

Composer:
---------
    composer require vitexsoftware/ease-fluentpdo

Linux
-----

For Debian, Ubuntu & friends please use repo:

```
echo "deb http://repo.vitexsoftware.com $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/vitexsoftware.list
sudo wget -O /etc/apt/trusted.gpg.d/vitexsoftware.gpg http://repo.vitexsoftware.com/keyring.gpg
sudo apt update
sudo apt install php-vitexsoftware-ease-fluentpdo
```

In this case please add this to your app composer.json:

```json
    "require": {
        "deb/ease-fluentpdo": "*"
    },
    "repositories": [
        {
            "type": "path",
            "url": "/usr/share/php/EaseSQL",
            "options": {
                "symlink": true
            }
        }
    ]
```


Framework Constants
===================

  * DB_TYPE      - pgsql|mysql|sqlsrv|sqlite
  * DB_HOST      - localhost is default 
  * DB_PORT      - database port 
  * DB_DATABASE  - database schema name
  * DB_USERNAME  - database user login name
  * DB_PASSWORD  - database user password
  * DB_SETUP     - database setup command (executed directly after connect)
  * DB_DEBUG     - log sql queries

Testing
-------

At first you need initialise create sql user & database with login and password 
from testing/phinx.yml and initialise testing database by **phinx migrate** 
command:

```shell
make phpunit
```

Or initalize another database and update .env file

```
composer update
cd tests
mysqladmin -u root -p create easetest
mysql -u root -p -e "GRANT ALL PRIVILEGES ON easetest.* TO easetest@localhost IDENTIFIED BY 'easetest'"
sudo -u postgres bash -c "psql -c \"CREATE USER easetest WITH PASSWORD 'easetest';\""
sudo -u postgres bash -c "psql -c \"create database easetest with owner easetest encoding='utf8' template template0;\""
make prepare
```

Building
--------

Simply run **make deb**

Links
=====

Homepage: https://www.vitexsoftware.cz/ease.php

GitHub: https://github.com/VitexSoftware/php-ease-fluentpdo
