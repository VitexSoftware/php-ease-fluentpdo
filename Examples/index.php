<?php

declare(strict_types=1);

/**
 * This file is part of the EaseFluentPDO package
 *
 * https://github.com/VitexSoftware/php-ease-fluentpdo
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const APP_NAME = 'EaseFluentPdoExample';

const EASE_LOGGER = 'console|\Ease\Logger\LogToSQL';

require_once \dirname(__DIR__).'/vendor/autoload.php';
\Ease\Shared::singleton()->loadConfig(__DIR__.'/.env', true);

$engine = new \Ease\SQL\Engine(null, ['myTable' => 'test']);

$inserted = $engine->insertToSQL(['name' => \Ease\Functions::randomString(), 'value' => \Ease\Functions::randomString()]);

if ($inserted) {
    $engine->addStatusMessage('Record #'.$inserted.' inserted');
}

$i = 0;

do {
    $engines[$i] = new \Ease\SQL\Engine(null, ['myTable' => 'test']);

    $inserted = $engines[$i]->insertToSQL(['name' => \Ease\Functions::randomString(), 'value' => \Ease\Functions::randomString()]);

    if ($inserted) {
        $engines[$i]->addStatusMessage('Record #'.$inserted.' inserted');
    }
} while (++$i <= 1000);

foreach ($engine->listingQuery()->order('name') as $row) {
    echo print_r($row, true);
}
