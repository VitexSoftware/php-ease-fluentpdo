<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Ease\SQL;

/**
 * Description of Engine
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class Engine extends \Ease\Brick
{

    use Orm;
    /**
     * Předvolená tabulka v SQL (součást identity).
     *
     * @var string
     */
    public $myTable = '';

    /**
     * Record create time column 
     * @var string 
     */
    public $createColumn = null;

    /**
     * 
     * @param mixed $identifier
     * @param array $options
     */
    public function __construct($identifier = null, $options = [])
    {
        $this->setUp($options);
        if (!is_null($identifier)) {
            $this->loadFromSQL($id);
        }
    }

    /**
     * SetUp Object to be ready for connect
     *
     * @param array $options Object Options (company,url,user,password,evidence,
     *                                       prefix,defaultUrlParams,debug)
     */
    public function setUp($options = [])
    {
        $this->setupProperty($options, 'dbType', 'DB_TYPE');
        $this->setupProperty($options, 'server', 'DB_HOST');
        $this->setupProperty($options, 'username', 'DB_USERNAME');
        $this->setupProperty($options, 'password', 'DB_PASSWORD');
        $this->setupProperty($options, 'database', 'DB_DATABASE');
        $this->setupProperty($options, 'port', 'DB_PORT');
        $this->setupProperty($options, 'connectionSettings', 'DB_SETUP');
        $this->setupProperty($options, 'myTable');
    }

    /**
     * 
     * @return \Envms\FluentPDO
     */
    public function listingQuery()
    {
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
    public function getColumnsFromSQL($columnsList, $conditions = null,
                                      $orderBy = null, $indexBy = null,
                                      $limit = null)
    {
        $result = [];
        $fluent = $this->listingQuery();

        if ($orderBy) {
            $fluent->orderBy($orderBy);
        }

        if ($limit) {
            $fluent->limit($limit);
        }

        $valuesRaw = $fluent->fetchAll();

        foreach ($valuesRaw as $rowId => $rowData) {
            foreach ( $rowData as $colName => $colValue){
                if(in_array($colName, $columnsList) ){
                    $result[$rowId][$colName] = $colValue;
                }
            }
        }

        return $indexBy ? \Ease\Functions::reindexArrayBy($result) : $result;
    }

    /**
     * Načte z SQL data k aktuálnímu $ItemID.
     *
     * @param int $itemID klíč záznamu
     *
     * @return array Results
     */
    public function getDataFromSQL($itemID = null)
    {
        if (is_null($itemID)) {
            $itemID = $this->getMyKey();
        }
        if (is_string($itemID)) {
            $itemID = "'".$this->dblink->addSlashes($itemID)."'";
        } else {
            $itemID = $this->dblink->addSlashes($itemID);
        } if (is_null($itemID)) {
            throw new \Ease\Exception('loadFromSQL: Unknown Key');
        }
        $cc       = $this->dblink->getColumnComma();
        $queryRaw = SQL::$sel.' * FROM '.$cc.$this->myTable.$cc.SQL::$whr.$cc.$this->getKeyColumn().$cc.' = '.$itemID;

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
                                  $columnToIndex = null)
    {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }

        if (is_null($limit)) {
            $limitCond = '';
        } else {
            $limitCond = SQL::$lmt.$limit;
        }
        if (is_null($orderByColumn)) {
            $orderByCond = '';
        } else {
            if (is_array($orderByColumn)) {
                $orderByCond = SQL::$ord.implode(',', $orderByColumn);
            } else {
                $orderByCond = SQL::$ord.$orderByColumn;
            }
        }
        if (is_null($columnsList)) {
            $cc      = $this->dblink->getColumnComma();
            $records = $this->dblink->queryToArray(SQL::$sel.'* FROM '.$cc.$tableName.$cc.' '.$limitCond.$orderByCond,
                $columnToIndex);
        } else {
            $records = $this->dblink->queryToArray(SQL::$sel.implode(',',
                    $columnsList).' FROM '.$tableName.$orderByCond.$limitCond,
                $columnToIndex);
        }

        return $records;
    }

    /**
     * Načte z SQL data k aktuálnímu $ItemID a použije je v objektu.
     *
     * @param int   $itemID     klíč záznamu
     *
     * @return array Results
     */
    public function loadFromSQL($itemID = null)
    {
        $rowsLoaded = null;
        if (is_null($itemID)) {
            $itemID = $this->getMyKey();
        }
        $sqlResult              = $this->listingQuery()->where($this->getMyKey(),
            $id);
        $this->multipleteResult = (count($sqlResult) > 1);

        if ($this->multipleteResult && is_array($sqlResult)) {
            $results = [];
            foreach ($sqlResult as $id => $data) {
                $this->takeData($data);
                $results[$id] = $this->getData();
            }
            $this->data = $results;
        } else {
            if (isset($sqlResult[0])) {
                $this->takeData($sqlResult[0]);
            }
        }
        if (!empty($this->data)) {
            $rowsLoaded = count($this->data);
        }

        return $rowsLoaded;
    }

    /**
     * Perform SQL record update.
     * Provede update záznamu do SQL.
     *
     * @param array $data
     *
     * @return int Id záznamu nebo null v případě chyby
     */
    public function updateToSQL($data = null)
    {
        if (is_null($this->myTable)) {
            return;
        }

        if (is_null($data)) {
            $data        = $this->getData();
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
                $this->addStatusMessage(get_class($this).':UpdateToSQL: Unknown keyColumn:'.$this->keyColumn.' '.
                    json_encode($data), 'error');

                return;
            }
        } else {
            $key = $data[$this->keyColumn];
            unset($data[$this->keyColumn]);
        }

        if (isset($this->myLastModifiedColumn) && !isset($data[$this->myLastModifiedColumn])) {
            $data[$this->myLastModifiedColumn] = 'NOW()';
        }

        $cc       = $this->dblink->getColumnComma();
        $queryRaw = SQL::$upd.$cc.$this->myTable.$cc.' SET '.$this->dblink->arrayToSetQuery($data).SQL::$whr.$cc.$this->keyColumn.$cc." = '".$this->dblink->addSlashes($key)."'";
        if ($this->dblink->exeQuery($queryRaw)) {
            if ($useInObject) {
                return $this->data[$this->keyColumn];
            } else {
                return $key;
            }
        }

        return;
    }

    /**
     * Uloží pole dat do SQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  keyColumn.
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int ID záznamu nebo null v případě neůspěchu
     */
    public function saveToSQL($data = null, $searchForID = false)
    {
        $result = null;
        if (is_null($data)) {
            $data = $this->getData();
        }

        if (count($data) < 1) {
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
                if (isset($data[$this->keyColumn]) && !is_null($data[$this->keyColumn])
                    && strlen($data[$this->keyColumn])) {
                    $result = $this->updateToSQL($data);
                } else {
                    $result = $this->insertToSQL($data);
                }
            }
        }

        if (!is_null($result)) {
            $this->setMyKey($result);
        }

        return $result;
    }

    /**
     * Insert record to SQL database.
     * Vloží záznam do SQL databáze.
     *
     * @param array $data
     *
     * @return int id of new row in database
     */
    public function insertToSQL($data = null)
    {
        $query = $this->getFluentPDO()->insertInto($this->getMyTable(), is_null($data) ? $this->getData() : $data  )->execute(); 
        return $this->getPdo()->lastInsertId();
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
        if (is_int($data)) {
            $data = [$this->getKeyColumn() => intval($data)];
        } else {
            if (is_null($data)) {
                $data = $this->getData();
            }
        }

        if (count($data)) {
            $cc = $this->dblink->getColumnComma();
            $this->dblink->exeQuery(SQL::$dlt.$cc.$this->myTable.$cc.SQL::$whr.$this->dblink->prepSelect($data));
            if ($this->dblink->getNumRows()) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->addStatusMessage('DeleteFromSQL: Unknown key.', 'error');

            return false;
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
                               $renameAs = null)
    {
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
     * Načte IDčeka z tabulky.
     *
     * @param string $tableName   jméno tabulky
     * @param string $keyColumn klíčovací sloupeček
     *
     * @return array list of IDs
     */
    public function getSQLList($tableName = null, $keyColumn = null)
    {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }
        if (is_null($keyColumn)) {
            $keyColumn = $this->keyColumn;
        }
        $cc        = $this->dblink->getColumnComma();
        $listQuery = SQL::$sel.$cc.$keyColumn.$cc.SQL::$frm.$tableName;
        return $this->dblink->queryToArray($listQuery);
    }

    /**
     * Vrací název aktuálně použivané SQL tabulky.
     *
     * @return string
     */
    public function getMyTable()
    {
        return $this->myTable;
    }

    /**
     * Nastaví aktuální pracovní tabulku pro SQL.
     *
     * @param string $myTable
     */
    public function setmyTable($myTable)
    {
        $this->myTable = $myTable;
        $this->setObjectIdentity(['myTable' => $myTable]);
    }

    /**
     * Vrátí počet položek tabulky v SQL.
     *
     * @param string $tableName pokud není zadáno, použije se $this->myTable
     *
     * @return int
     */
    public function getSQLItemsCount($tableName = null)
    {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }

        return $this->dblink->queryToValue(SQL::$sel.'COUNT('.$this->keyColumn.') FROM '.$tableName);
    }

    /**
     * Prohledá zadané slupečky.
     *
     * @param string $searchTerm
     * @param array  $columns
     */
    public function searchColumns($searchTerm, $columns)
    {
        $sTerm     = $this->dblink->addSlashes($searchTerm);
        $conditons = [];
        foreach ($columns as $column) {
            $conditons[] = '`'.$column.'` LIKE \'%'.$sTerm.'%\'';
        }

        return $this->dblink->queryToArray(SQL::$sel.'* FROM '.$this->myTable.SQL::$whr.implode(' OR ',
                    $conditons));
    }
}
