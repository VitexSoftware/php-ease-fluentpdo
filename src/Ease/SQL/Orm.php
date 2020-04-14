<?php

/**
 * Object Relation Model Trait
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018 Vitex@hippy.cz (G)
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
     * Nastavení vlastností přípojení.
     *
     * @var array
     */
    public $connectionSettings = [];

    /**
     * Objekt pro práci s SQL.
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
     * @param array $options Object Options (company,url,user,password,evidence,
     *                                       prefix,defaultUrlParams,debug)
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
    }

    /**
     * Perform connect to database.
     *
     * @return \PDO SQL connector
     */
    public function pdoConnect($options = []) {
        $result = false;
        if (is_null($this->dbType)) {
            $this->setUp($options);
        }
        if (is_null($this->dbType)) {
            $result = null;
        } else {
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
                case 'sqlite':
                    $result = new \PDO($this->dbType . ':' . $this->database);
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
        }

        return $result;
    }

    /**
     * (init &) Get PDO instance
     * 
     * @return \PDO
     */
    public function getPdo() {
        if (!$this->pdo instanceof \PDO) {
            $this->pdo = $this->pdoConnect();
        }
        return $this->pdo;
    }

    /**
     * SQL Builder
     * 
     * @return \Envms\FluentPDO
     */
    public function getFluentPDO() {
        if (!$this->fluent instanceof \Envms\FluentPDO\Query) {
            $this->fluent = new \Envms\FluentPDO\Query($this->getPdo());
            $this->fluent->exceptionOnError = true;
            $this->fluent->debug = $this->debug;
        }
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
            $fluent = $this->listingQuery();
        } else {
            $fluent = $this->listingQuery()->where($conditions);
        }

        if ($orderBy) {
            $fluent->orderBy($orderBy);
        }

        if ($limit) {
            $fluent->limit($limit);
        }

        $valuesRaw = $fluent->fetchAll();

        if (!empty($valuesRaw)) {
            foreach ($valuesRaw as $rowId => $rowData) {
                foreach ($rowData as $colName => $colValue) {
                    if (($columnsList == ['*']) || in_array($colName, $columnsList)) {
                        \Ease\Functions::divDataArray($valuesRaw[$rowId], $result[$rowId], $colName);
                    }
                }
            }
        }
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
        if (is_null($itemID)) {
            $itemID = $this->getMyKey();
        }
        if (is_string($itemID)) {
            $itemID = "'" . $this->dblink->addSlashes($itemID) . "'";
        } else {
            $itemID = $this->dblink->addSlashes($itemID);
        } if (is_null($itemID)) {
            throw new \Ease\Exception('loadFromSQL: Unknown Key');
        }
        $cc = $this->dblink->getColumnComma();
        $queryRaw = SQL::$sel . ' * FROM ' . $cc . $this->myTable . $cc . SQL::$whr . $cc . $this->getKeyColumn() . $cc . ' = ' . $itemID;

        return $this->dblink->queryToArray($queryRaw);
    }

    /**
     * Vrátí z SQL všechny záznamy.
     *
     * @param string $tableName     jméno tabulky
     * @param array  $columnsList   získat pouze vyjmenované sloupečky
     * @param int    $limit         SQL Limit na vracene radky
     * @param string $orderByColumn jméno sloupečku pro třídění
     * @param string $columnToIndex jméno sloupečku pro indexaci
     *
     * @return array
     */
    public function getAllFromSQL($tableName = null, $columnsList = null,
            $limit = null, $orderByColumn = null,
            $columnToIndex = null) {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }

        if (is_null($limit)) {
            $limitCond = '';
        } else {
            $limitCond = SQL::$lmt . $limit;
        }
        if (is_null($orderByColumn)) {
            $orderByCond = '';
        } else {
            if (is_array($orderByColumn)) {
                $orderByCond = SQL::$ord . implode(',', $orderByColumn);
            } else {
                $orderByCond = SQL::$ord . $orderByColumn;
            }
        }
        if (is_null($columnsList)) {
            $cc = $this->dblink->getColumnComma();
            $records = $this->dblink->queryToArray(SQL::$sel . '* FROM ' . $cc . $tableName . $cc . ' ' . $limitCond . $orderByCond,
                    $columnToIndex);
        } else {
            $records = $this->dblink->queryToArray(SQL::$sel . implode(',',
                            $columnsList) . ' FROM ' . $tableName . $orderByCond . $limitCond,
                    $columnToIndex);
        }

        return $records;
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
        return $this->loadFromSQL($this->getMyKey());
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
     * @param array $data
     *
     * @return int Id záznamu nebo null v případě chyby
     */
    public function updateToSQL($data = null) {
        if (is_null($this->myTable)) {
            return;
        }

        if (is_null($data)) {
            $data = $this->getData();
            $useInObject = true;
        } else {
            $useInObject = false;
        }

        if (!count($data)) {
            $this->addStatusMessage(_('UpdateToSQL: Missing data'), 'error');

            return;
        }

        if (!isset($data[$this->keyColumn])) {
            $key = $this->getMyKey();
            if (is_null($key)) {
                $this->addStatusMessage(get_class($this) . ':UpdateToSQL: Unknown keyColumn:' . $this->keyColumn . ' ' .
                        json_encode($data), 'error');

                return;
            }
        } else {
            $key = $data[$this->keyColumn];
            unset($data[$this->keyColumn]);
        }

        if (isset($this->lastModifiedColumn) && !isset($data[$this->lastModifiedColumn])) {
            $data[$this->lastModifiedColumn] = date("Y-m-d H:i:s");
        }

        return $this->getFluentPDO()->update($this->getMyTable())->set($data)->where($this->getKeyColumn(), $key)->execute();
    }

    /**
     * Uloží pole dat do SQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  keyColumn.
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToSQL($data = null, $searchForID = false) {
        $result = null;
        if (is_null($data)) {
            $data = $this->getData();
        }

        if (empty($data)) {
            $this->addStatusMessage('SaveToSQL: Missing data', 'error');
        } else {
            if ($searchForID) {
                if ($this->getMyKey($data)) {
                    $rowsFound = $this->getColumnsFromSQL($this->getKeyColumn(),
                            [$this->getKeyColumn() => $this->getMyKey($data)]);
                } else {
                    $rowsFound = $this->getColumnsFromSQL([$this->getKeyColumn()],
                            $data);
                    if (count($rowsFound)) {
                        if (is_numeric($rowsFound[0][$this->getKeyColumn()])) {
                            $data[$this->getKeyColumn()] = (int) $rowsFound[0][$this->getKeyColumn()];
                        } else {
                            $data[$this->getKeyColumn()] = $rowsFound[0][$this->getKeyColumn()];
                        }
                    }
                }

                if (count($rowsFound)) {
                    $result = $this->updateToSQL($data);
                } else {
                    $result = $this->insertToSQL($data);
                }
            } else {
                if (isset($data[$this->keyColumn]) && !is_null($data[$this->keyColumn]) && strlen($data[$this->keyColumn])) {
                    $result = $this->updateToSQL($data);
                } else {
                    $result = $this->insertToSQL($data);
                }
            }
        }

        return $result;
    }

    /**
     * Insert record to SQL database.
     * Vloží záznam do SQL databáze.
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
            $this->getFluentPDO()->insertInto($this->getMyTable(), $data)->execute();
            $insertId = $this->getPdo()->lastInsertId();
            $this->setMyKey(intval($insertId));
            return is_null($insertId) ? null : intval($insertId);
        } catch (\Envms\FluentPDO\Exception $exc) {
            $this->addStatusMessage($exc->getMessage(), 'error');
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
        }
    }

    /**
     * Assign data from field to data array.
     * Přiřadí data z políčka do pole dat.
     *
     * @param array  $data      asociativní pole dat
     * @param string $column    název položky k převzetí
     * @param bool   $mayBeNull nahrazovat chybejici hodnotu nullem ?
     * @param string $renameAs  název cílového políčka
     *
     * @return mixed převzatá do pole
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

                return;
            }
        }
    }

    /**
     * 
     * @return string
     */
    public function getMyTable() {
        return $this->myTable;
    }

    /**
     * 
     * @param string $tablename
     */
    public function setMyTable($tablename) {
        $this->myTable = $tablename;
    }

}
