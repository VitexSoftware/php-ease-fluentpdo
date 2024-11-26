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

namespace Ease\SQL;

/**
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
trait Orm
{
    /**
     * Server Host or IP.
     */
    public ?string $server = null;

    /**
     * DB Login.
     */
    public ?string $dbLogin = null;

    /**
     * DB password.
     */
    public ?string $dbPass = null;

    /**
     * Database to connect by default.
     */
    public string $database = '';

    /**
     * Database port.
     */
    public ?string $port = null;

    /**
     * Type of used database.
     *
     * @var string mysql|pgsql|..
     */
    public string $dbType = '';

    /**
     * Default connection settings.
     *
     * @var array<string, string>|string
     */
    public $dbSettings = [];

    /**
     * Default connection setup.
     *
     * @var array<string, string>
     */
    public array $connectionSetup = [];

    /**
     * PDO Driver object.
     */
    public ?\PDO $pdo = null;

    /**
     * Fluent Query.
     */
    public ?\Envms\FluentPDO\Query $fluent;

    /**
     * Poslední Chybová zpráva obdržená od SQL serveru.
     */
    public array $errorInfo = [];

    /**
     * Kod SQL chyby.
     */
    protected int $errorNumber;

    /**
     * Only one rows returned ?
     */
    private bool $multipleteResult;

    /**
     * SetUp database connections.
     *
     * @param array<string, string> $options
     */
    public function setUp(array $options = []): bool
    {
        $this->setUpDb($options);

        return true;
    }

    /**
     * SetUp Object to be ready for connect.
     *
     * @param array<string, string> $options Object Options (dbType,server,username,password,database,
     *                                       port,connectionSettings,myTable,debug)
     */
    public function setUpDb($options = []): void
    {
        $this->setupProperty($options, 'dbType', 'DB_TYPE');       // Ease
        $this->setupProperty($options, 'dbType', 'DB_CONNECTION'); // Laralvel
        $this->setupProperty($options, 'server', 'DB_HOST');
        $this->setupProperty($options, 'dbLogin', 'DB_USERNAME');
        $this->setupProperty($options, 'dbPass', 'DB_PASSWORD');
        $this->setupProperty($options, 'database', 'DB_DATABASE');
        $this->setupProperty($options, 'port', 'DB_PORT');
        $this->setupProperty($options, 'connectionSetup', 'DB_SETUP');
        $this->setupProperty($options, 'dbSettings', 'DB_SETTINGS');
        $this->setupProperty($options, 'myTable');
        $this->setupProperty($options, 'debug', 'DB_DEBUG');
    }

    /**
     * Perform connect to database.
     *
     * @param mixed $options
     *
     * @return \PDO SQL connector
     */
    public function pdoConnect($options = []): \PDO
    {
        $result = false;
        $this->setUp($options);
        $dbSettings = \is_array($this->dbSettings) ? implode(';', $this->dbSettings) : ';'.$this->dbSettings;

        switch ($this->dbType) {
            case 'mysql':
                $result = new \PDO(
                    $this->dbType.':dbname='.$this->database.';host='.$this->server.';port='.$this->port.';charset=utf8'.$dbSettings,
                    $this->dbLogin,
                    $this->dbPass,
                    [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'', \PDO::ATTR_PERSISTENT => true],
                );

                break;
            case 'pgsql':
                $result = new \PDO(
                    $this->dbType.':dbname='.$this->database.';host='.$this->server.';port='.$this->port.$dbSettings,
                    $this->dbLogin,
                    $this->dbPass,
                );

                if (\is_object($result)) {
                    $result->exec("SET NAMES 'UTF-8'");
                }

                break;
            case 'sqlsrv': // https://www.php.net/manual/en/ref.pdo-sqlsrv.connection.php
                $result = new \PDO(
                    $this->dbType.':Server='.$this->server.(isset($this->port) ? ','.$this->port : '').';Database='.$this->database.$dbSettings,
                    $this->dbLogin,
                    $this->dbPass,
                );

                break;
            case 'sqlite3':
                $this->dbType = 'sqlite';
                // no break
            case 'sqlite':
                if (file_exists($this->database)) {
                    $result = new \PDO($this->dbType.':'.$this->database);
                    $result->exec('PRAGMA journal_mode = wal;');
                } else {
                    throw new \PDOException(sprintf(_('unable to open database file: %s'), $this->database[0] === '/' ? $this->database : getcwd().'/'.$this->database));
                }

                break;

            default:
                throw new \Exception(_('Unimplemented Database type').': '.$this->dbType);
        }

        if ($result instanceof \PDO) {
            $errorNumber = $result->errorCode();

            if (null !== $errorNumber && ($errorNumber !== '00000') && ($errorNumber !== '01000')) { // SQL_SUCCESS_WITH_INFO
                $this->addStatusMessage(
                    'Connect: error #'.$errorNumber.' '.json_encode($result->errorInfo()),
                    'error',
                );
            } else {
                if (!empty($this->connectionSetup)) {
                    foreach ($this->connectionSetup as $setName => $setValue) {
                        if (\strlen($setName)) {
                            $this->getPdo()->exec("SET {$setName} {$setValue}");
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * (init &) Get PDO instance.
     *
     * @param array $propeties $name Connection Properties
     *
     * @return \PDO
     */
    public function getPdo($propeties = [])
    {
        if (($this->pdo instanceof \PDO) === false) {
            $this->pdo = $this->pdoConnect($propeties);
        }

        return $this->pdo;
    }

    /**
     * SQL Builder.
     *
     * @param bool $read  convert mode for select
     * @param bool $write convert mode for insert
     *
     * @return \Envms\FluentPDO\Query
     */
    public function getFluentPDO(bool $read = false, bool $write = false)
    {
        if (!isset($this->fluent) || !($this->fluent instanceof \Envms\FluentPDO\Query)) {
            $this->fluent = new \Envms\FluentPDO\Query($this->getPdo());
            $this->fluent->exceptionOnError = true;
            $this->fluent->debug = $this->debug ? function ($fluent): void {
                new Debugger($fluent, $this);
            } : false;
        }

        if ($this->dbType !== 'sqlite') { // HotFix for https://github.com/envms/fluentpdo/issues/289
            $this->fluent->convertTypes($read, $write);
        }

        return $this->fluent;
    }

    /**
     * Basic Query to return all.
     */
    public function listingQuery(): \Envms\FluentPDO\Queries\Select
    {
        return $this->getFluentPDO(true)->from($this->getMyTable());
    }

    /**
     * Get database columns values by conditions.
     *
     * @param array<string>    $columnsList column names listing
     * @param array|int|string $conditions  conditions or ID
     * @param array|string     $orderBy     sort by
     * @param string           $indexBy     result keys by row keys
     * @param int              $limit       maximum number of results
     *
     * @return array
     */
    public function getColumnsFromSQL(
        array $columnsList,
        $conditions = null,
        $orderBy = null,
        $indexBy = null,
        $limit = null
    ) {
        $result = [];

        if (empty($conditions)) {
            $fluent = $this->listingQuery()->select($columnsList, true);
        } else {
            $fluent = $this->listingQuery()->select($columnsList, true)->where($conditions);
        }

        if ($orderBy) {
            $fluent->orderBy($orderBy);
        }

        if ($limit) {
            $fluent->limit($limit);
        }

        $result = $fluent->fetchAll();

        return empty($result) ? $result : ($indexBy ? \Ease\Functions::reindexArrayBy($result, $indexBy) : $result);
    }

    /**
     * Load actual $ItemID SQL data.
     *
     * @param int $itemID record key
     *
     * @return array Results
     */
    public function getDataFromSQL($itemID = null, array $columnsList = ['*'])
    {
        return $this->listingQuery()->select($columnsList, true)->where([$this->getKeyColumn() => $itemID])->fetchAll();
    }

    /**
     * Retrieves data from SQL for the current $ItemID and uses it in the object.
     *
     * @param array|int $itemID Record key
     *
     * @return array Results
     */
    public function loadFromSQL($itemID)
    {
        $rowsLoaded = null;
        $sqlResult = $this->listingQuery()->where(\is_array($itemID) ? $itemID : [$this->getKeyColumn() => $itemID])->fetchAll();
        $this->multipleteResult = (\count($sqlResult) > 1);

        if ($this->multipleteResult && \is_array($sqlResult)) {
            $results = [];

            foreach ($sqlResult as $id => $data) {
                $this->takeData($data);
                $results[$id] = $this->getData();
            }

            $this->data = $results;
        } else {
            if (!empty($sqlResult)) {
                $this->takeData(current($sqlResult));
            }
        }

        if (!empty($this->data)) {
            $rowsLoaded = \count($this->data);
        }

        return $rowsLoaded;
    }

    /**
     * Reload current record from Database.
     *
     * @return bool
     */
    public function dbreload()
    {
        return $this->loadFromSQL([$this->getMyTable().'.'.$this->getKeyColumn() => $this->getMyKey()]);
    }

    /**
     * Insert current data into Database and load actual record data back.
     *
     * @param array $data Initial data to save
     *
     * @return bool Operation success
     */
    public function dbsync($data = null)
    {
        return $this->saveToSQL(null === $data ? $this->getData() : $data) && $this->dbreload();
    }

    /**
     * Perform SQL record update.
     * Provede update záznamu do SQL.
     *
     * @param array $data      to save
     * @param array $conditons Update condition
     *
     * @return int Id záznamu nebo null v případě chyby
     */
    public function updateToSQL($data = null, $conditons = [])
    {
        if (null === $data) {
            $data = $this->getData();
        }

        $keyColumn = $this->getKeyColumn();

        if (isset($data[$keyColumn])) {
            $key = $data[$keyColumn];
            unset($data[$keyColumn]);
        } else {
            $key = false;
        }

        if (isset($this->lastModifiedColumn) && !isset($data[$this->lastModifiedColumn])) {
            $data[$this->lastModifiedColumn] = date('Y-m-d H:i:s');
        }

        return $this->getFluentPDO(false, true)->update($this->getMyTable())->set($data)->where(empty($conditons) && $key ? [$this->getKeyColumn() => $key] : $conditons)->execute() ? $key : null;
    }

    /**
     * Save data array to SQL.
     *
     * @param array $data asociativní pole dat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToSQL($data = null)
    {
        $result = null;

        if (null === $data) {
            $data = $this->getData();
        }

        $keyColumn = $this->getKeyColumn();

        if (!$this->getMyKey($data) && $this->getMyKey()) {
            $data[$keyColumn] = $this->getMyKey();
        }

        if (\array_key_exists($keyColumn, $data) && !empty($data[$keyColumn])) {
            $result = $this->updateToSQL($data);
        } else {
            $result = $this->insertToSQL($data);
        }

        return $result;
    }

    /**
     * Insert record to SQL database.
     *
     * @param array $data
     *
     * @return null|int id of new row in database
     */
    public function insertToSQL($data = null)
    {
        if (null === $data) {
            $data = $this->getData();
        }

        if ($this->createColumn && !isset($data[$this->createColumn])) {
            $data[$this->createColumn] = date('Y-m-d H:i:s');
        }

        try {
            $this->getFluentPDO(false, true)->insertInto($this->getMyTable(), $data)->execute();
            $insertId = $this->getPdo()->lastInsertId();
            $this->setMyKey((int) $insertId);

            return null === $insertId ? null : (int) $insertId;
        } catch (\Envms\FluentPDO\Exception $exc) {
            $this->addStatusMessage($exc->getMessage(), 'error');
            $this->addStatusMessage(json_encode($data), 'debug');

            throw $exc;
        }
    }

    /**
     * Smaže záznam z SQL.
     *
     * @param array|int $data
     *
     * @return bool
     */
    public function deleteFromSQL($data = null)
    {
        if (null === $data) {
            $data = $this->getData();
        }

        try {
            if (\is_array($data)) {
                $result = $this->getFluentPDO()->deleteFrom($this->getMyTable())->where($data)->execute();
            } else {
                $result = $this->getFluentPDO()->deleteFrom($this->getMyTable(), $data)->execute();
            }

            return $result;
        } catch (\Envms\FluentPDO\Exception $exc) {
            $this->addStatusMessage($exc->getMessage(), 'error');

            throw $exc;
        }
    }

    /**
     * Assign data from field to data array.
     *
     * @deprecated since version 0.1
     *
     * @param array  $data      asociativní pole dat
     * @param string $column    název položky k převzetí
     * @param bool   $mayBeNull nahrazovat chybejici hodnotu nullem ?
     * @param string $renameAs  název cílového políčka
     *
     * @return null|array array taken or not
     */
    public function takeToData(
        $data,
        $column,
        $mayBeNull = false,
        $renameAs = null
    ) {
        if (isset($data[$column])) {
            if (null !== $renameAs) {
                $this->setDataValue($renameAs, $data[$column]);
            } else {
                $this->setDataValue($column, $data[$column]);
            }

            return $data[$column];
        }

        if (null !== $mayBeNull) {
            $this->setDataValue($column, null);

            return null;
        }
    }

    /**
     * We work with table.
     *
     * @return string
     */
    public function getMyTable()
    {
        return $this->myTable;
    }

    /**
     * Specify used table by name.
     *
     * @param string $tablename
     */
    public function setMyTable($tablename): void
    {
        $this->myTable = $tablename;
    }

    /**
     * Check for argument presence.
     *
     * @param array|int|string $data int for ID column, use string to search in nameColumn
     *
     * @return int number of occurrences
     */
    public function recordExists($data = [])
    {
        switch (\gettype($data)) {
            case 'string':
                $cond = [$this->nameColumn => $data];

                break;
            case 'integer':
                $cond = [$this->keyColumn => $data];

                break;

            default:
                $cond = $data;

                break;
        }

        return $this->listingQuery()->where($cond)->count();
    }
}
