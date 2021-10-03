<?php

/**
 * Please update .env first for your database
 * 
 * Then prepare tables:
 * 
 *      composer update   
 * 	vendor/bin/phinx migrate -c Examples/phinx-adapter.php
 * 	vendor/bin/phinx seed:run -c Examples/phinx-adapter.php
 * 
 */
const APP_NAME = "EaseFluentPdoExample";

const EASE_LOGGER = 'console|\Ease\Logger\LogToSQL';

require_once dirname(__DIR__) . '/vendor/autoload.php';
\Ease\Shared::singleton()->loadConfig(__DIR__ . '/.env', true);

$engine = new \Ease\SQL\Engine(null, ['myTable' => 'test']);

$inserted = $engine->insertToSQL(['name' => \Ease\Functions::randomString(), 'value' => \Ease\Functions::randomString()]);
if ($inserted) {
    $engine->addStatusMessage(sprintf('Record #' . $inserted . ' inserted'));
}

$i = 0;
do {
    $engines[$i] = new \Ease\SQL\Engine(null, ['myTable' => 'test']);

    $inserted = $engines[$i]->insertToSQL(['name' => \Ease\Functions::randomString(), 'value' => \Ease\Functions::randomString()]);
    if ($inserted) {
        $engines[$i]->addStatusMessage(sprintf('Record #' . $inserted . ' inserted'));
    }
} while (++$i <= 1000);

/**
 * 
 */
foreach ($engine->listingQuery()->order('name') as $row) {
    echo print_r($row, true);
}
