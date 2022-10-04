<?php

/**
 *      composer update   
 * 	vendor/bin/phinx migrate -c Examples/phinx-adapter.php
 * 	vendor/bin/phinx seed:run -c Examples/phinx-adapter.php
 */
use Ease\Functions;
use Ease\SQL\Engine;
use Phinx\Config\Config;
use Phinx\Db\Action\DropTable;
use Phinx\Db\Table\Table;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

require_once dirname(__DIR__) . '/vendor/autoload.php';

define('APP_NAME', 'EaseFluentPDOTest');
define('APP_DEBUG', 'true');
define('DB_CONNECTION', 'sqlite');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '0');
define('DB_DATABASE', __DIR__ . '/test.sqlite');
define('DB_USERNAME', 'phpunit');
define('DB_PASSWORD', 'phpunit');

if (file_exists(\Ease\Functions::cfg('DB_DATABASE'))) {
    unlink(\Ease\Functions::cfg('DB_DATABASE'));
    touch(\Ease\Functions::cfg('DB_DATABASE'));
} else {
    new DropTable(new Table('test'));
    new DropTable(new Table('log'));
    new DropTable(new Table('phinxlog'));
}

$pdo = new Engine();
$configArray['paths']['migrations'] = __DIR__ . '/migrations';
$configArray['paths']['seeds'] = __DIR__ . '/seeds';
$configArray['environments']['test'] = [
    'adapter' => Functions::cfg('DB_CONNECTION'),
    'connection' => $pdo->getPDO()
];
$config = new Config($configArray);
$manager = new Manager($config, new StringInput(' '), new ConsoleOutput(OutputInterface::VERBOSITY_NORMAL));
$manager->migrate('test');
$manager->seed('test');

class PDOTester extends \Ease\SQL\PDO {

    use \Ease\SQL\Orm;

    public $myTable = 'test';

    public function connect() {
        $this->getPdo();
        parent::connect();
    }

}

class SQLTester extends \Ease\SQL\SQL {

    public $myTable = 'test';

    public function exeQuery($query) {
        
    }

}
