<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

define('APP_NAME', 'EaseFluentPDO');
define('APP_DEBUG', 'true');
define('DB_CONNECTION', 'sqlite');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '0');
define('DB_DATABASE', __DIR__ . '/test.sqlite');
define('DB_USERNAME', 'phpunit');
define('DB_PASSWORD', 'phpunit');




new \Phinx\Db\Action\DropTable(new \Phinx\Db\Table\Table('test'));
