<?php

/**
 * Object Relation Model Trait
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2021 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

/**
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
trait Orm {

    /**
     * IP serveru.
     *
     * @var string
     */
    public $server = null;

    /**
     * DB Login.
     *
     * @var string
     */
    public $username = null;

    /**
     * DB heslo.
     *
     * @var string
     */
    public $password = null;

    /**
     * Database to connect by default.
     *
     * @var string
     */
    public $database = null;

    /**
     * Database port.
     *
     * @var string
     */
    public $port = null;

    /**
     * Type of used database.
     *
     * @var string mysql|pgsql|..
     */
    public $dbType;

    /**
     * Default connection settings.
     *
     * @var array
     */
    public $connectionSettings = [];

    /**
     * PDO Driver object
     *
     * @var PDO
     */
    public $pdo = null;

    /**
     *
     * @var Fluent 
     */
    public $fluent = null;

    /**
     * Poslední Chybová zpráva obdržená od SQL serveru.
     *
     * @var array
     */
    public $errorInfo = [];

    /**
     * Kod SQL chyby.
     *
     * @var int
     */
    public $errorNumber = null;

    /**
     * SetUp Object to be ready for connect
     *
     * @param array $options Object Options (dbType,server,username,password,database,
     *                                       port,connectionSettings,myTable,debug)
     */
    public function setUp($options = []) {
        $this->setupProperty($options, 'dbType', 'DB_CONNECTION'); //Laralvel 
        $this->setupProperty($options, 'dbType', 'DB_TYPE');       //Ease
        $this->setupProperty($options, 'server', 'DB_HOST');
        $this->setupProperty($options, 'username', 'DB_USERNAME');
        $this->setupProperty($options, 'password', 'DB_PASSWORD');
        $this->setupProperty($options, 'database', 'DB_DATABASE');
        $this->setupProperty($options, 'port', 'DB_PORT');
        $this->setupProperty($options, 'connectionSettings', 'DB_SETUP');
        $this->setupProperty($options, 'myTable');
        $this->setupProperty($options, 'debug', 'DEBUG');
    }

    /**
     * Perform connect to database.
     *
     * @return \PDO SQL connector
     */
    public function pdoConnect($options = []) {
        $result = false;
        $this->setUp($options);
        switch ($this->dbType) {
            case 'mysql':
                $result = new \PDO($this->dbType . ':dbname=' . $this->database . ';host=' . $this->server . ';port=' . $this->port . ';charset=utf8',
                        $this->username, $this->password,
                        [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'']);
                break;
            case 'pgsql':
                $result = new \PDO($this->dbType . ':dbname=' . $this->database . ';host=' . $this->server . ';port=' . $this->port,
                        $this->username, $this->password);
                if (is_object($result)) {
                    $result->exec("SET NAMES 'UTF-8'");
                }
                break;
            case 'sqlsrv': // https://www.php.net/manual/en/ref.pdo-sqlsrv.connection.php
                $result = new \PDO($this->dbType . ':Server=' . $this->server . (isset($this->port) ? ',' . $this->port : '') . ';Database=' . $this->database,
                        $this->username, $this->password);
                break;
            case 'sqlite3':
                $this->dbType = 'sqlite';
            case 'sqlite':
                if (file_exists($this->database)) {
                    $result = new \PDO($this->dbType . ':' . $this->database);
                    $result->exec('PRAGMA journal_mode = wal;');
                } else {
                    throw new \PDOException(sprintf(_('unable to open database file: %s'), $this->database[0] == '/' ? $this->database : getcwd() . '/' . $this->database));
                }
                break;
            default:
                throw new \Ease\Exception(_('Unimplemented Database type') . ': ' . $this->dbType);
                break;
        }

        if ($result instanceof \PDO) {
            $errorNumber = $result->errorCode();

            if (!is_null($errorNumber) && ($errorNumber != '00000') && ($errorNumber != '01000')) { // SQL_SUCCESS_WITH_INFO
                $this->addStatusMessage('Connect: error #' . $errorNumber . ' ' . json_encode($result->errorInfo()),
                        'error');
            } else {
                if (!empty($this->connectionSettings))
                    foreach ($this->connectionSettings as $setName => $SetValue) {
                        if (strlen($setName)) {
                            $this->getPdo->exec("SET $setName $SetValue");
                        }
                    }
            }
        }

        return $result;
    }

    /**
     * (init &) Get PDO instance
     * 
     * @param array $properties $name Connection Properties
     * 
     * @return \PDO
     */
    public function getPdo($propeties = []) {
        if (!$this->pdo instanceof \PDO) {
            $this->pdo = $this->pdoConnect($propeties);
        }
        return $this->pdo;
    }

    /**
     * SQL Builder
     * 
     * @param bool $read    convert mode for select
     * @param bool $write   convert mode for insert
     * 
     * @return \Envms\FluentPDO
     */
    public function getFluentPDO(bool $read = false, bool $write = false) {
        if (!$this->fluent instanceof \Envms\FluentPDO\Query) {
            $this->fluent = new \Envms\FluentPDO\Query($this->getPdo());
            $this->fluent->exceptionOnError = true;
            $this->fluent->debug = $this->debug ? function($fluent) { new Debugger($fluent); } : false;
        }
        $this->fluent->convertTypes($read, $write);
        return $this->fluent;
    }

    /**
     * 
     * @return \Envms\FluentPDO
     */
    public function listingQuery() {
        return $this->getFluentPDO()->from($this->getMyTable());
    }

    /**
     * Vrací z databáze sloupečky podle podmínek.
     *
     * @param array            $columnsList seznam položek
     * @param array|int|string $conditions  pole podmínek nebo ID záznamu
     * @param array|string     $orderBy     třídit dle
     * @param string           $indexBy     klice vysledku naplnit hodnotou ze
     *                                      sloupečku
     * @param int              $limit       maximální počet vrácených záznamů
     *
     * @return array
     */
    public function getColumnsFromSQL(array $columnsList, $conditions = null,
            $orderBy = null, $indexBy = null,
            $limit = null) {
        $result = [];

        if (empty($conditions)) {
            $fluent = $this->listingQuery()->select($columnsList,true);
        } else {
            $fluent = $this->listingQuery()->select($columnsList,true)->where($conditions);
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
     * Načte z SQL data k aktuálnímu $ItemID.
     *
     * @param int $itemID klíč záznamu
     *
     * @return array Results
     */
    public function getDataFromSQL($itemID = null) {
        
    }

    /**
     * Načte z SQL data k aktuálnímu $ItemID a použije je v objektu.
     *
     * @param int|array   $itemID     klíč záznamu
     *
     * @return array Results
     */
    public function loadFromSQL($itemID) {
        $rowsLoaded = null;
        $sqlResult = $this->listingQuery()->where(is_array($itemID) ? $itemID : [$this->getKeyColumn() => $itemID])->fetchAll();
        $this->multipleteResult = (count($sqlResult) > 1);

        if ($this->multipleteResult && is_array($sqlResult)) {
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
            $rowsLoaded = count($this->data);
        }

        return $rowsLoaded;
    }

    /**
     * Reload current record from Database
     * 
     * @return boolean 
     */
    public function dbreload() {
        return $this->loadFromSQL([$this->getMyTable() . '.' . $this->getKeyColumn() => $this->getMyKey()]);
    }

    /**
     * Insert current data into Database and load actual record data back
     *
     * @param array $data Initial data to save
     * 
     * @return boolean Operation success
     */
    public function dbsync($data = null) {
        return $this->saveToSQL(is_null($data) ? $this->getData() : $data) && $this->dbreload();
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
    public function updateToSQL($data = null, $conditons = []) {
        if (is_null($data)) {
            $data = $this->getData();
        }

        $keyColumn = $this->getKeyColumn();
        if (isset($data[$keyColumn])) {
            $key = $data[$keyColumn];
            unset($data[$keyColumn]);
        }

        if (isset($this->lastModifiedColumn) && !isset($data[$this->lastModifiedColumn])) {
            $data[$this->lastModifiedColumn] = date("Y-m-d H:i:s");
        }
        return $this->getFluentPDO(false, true)->update($this->getMyTable())->set($data)->where(empty($conditons) ? [$this->getKeyColumn() => $key] : $conditons)->execute() ? $key : null;
    }

    /**
     * Uloží pole dat do SQL.
     *
     * @param array $data        asociativní pole dat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToSQL($data = null) {
        $result = null;
        if (is_null($data)) {
            $data = $this->getData();
        }
        $keyColumn = $this->getKeyColumn();
        if (!$this->getMyKey($data) && $this->getMyKey()) {
            $data[$keyColumn] = $this->getMyKey();
        }
        if (isset($data[$keyColumn]) && !is_null($data[$keyColumn]) && strlen($data[$keyColumn])) {
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
     * @return int|null id of new row in database
     */
    public function insertToSQL($data = null) {
        if (is_null($data)) {
            $data = $this->getData();
        }
        if ($this->createColumn && !isset($data[$this->createColumn])) {
            $data[$this->createColumn] = date("Y-m-d H:i:s");
        }
        try {
            $this->getFluentPDO(false, true)->insertInto($this->getMyTable(), $data)->execute();
            $insertId = $this->getPdo()->lastInsertId();
            $this->setMyKey(intval($insertId));
            return is_null($insertId) ? null : intval($insertId);
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
    public function deleteFromSQL($data = null) {
        if (is_null($data)) {
            $data = $this->getData();
        }
        try {
            if (is_array($data)) {
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
    public function takeToData($data, $column, $mayBeNull = false,
            $renameAs = null) {
        if (isset($data[$column])) {
            if (!is_null($renameAs)) {
                $this->setDataValue($renameAs, $data[$column]);
            } else {
                $this->setDataValue($column, $data[$column]);
            }

            return $data[$column];
        } else {
            if (!is_null($mayBeNull)) {
                $this->setDataValue($column, null);

                return null;
            }
        }
    }

    /**
     * We work with table
     * 
     * @return string
     */
    public function getMyTable() {
        return $this->myTable;
    }

    /**
     * Specify used table by name
     * 
     * @param string $tablename
     */
    public function setMyTable($tablename) {
        $this->myTable = $tablename;
    }

}
