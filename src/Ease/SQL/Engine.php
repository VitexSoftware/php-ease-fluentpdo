<?php

/**
 * Database Engine class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2020 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

/**
 * Description of Engine
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class Engine extends \Ease\Brick {

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
    public function __construct($identifier = null, $options = []) {
        $this->setUp($options);
        if (!is_null($identifier)) {
            $this->loadFromSQL($identifier);
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
    public function getSQLList($tableName = null, $keyColumn = null) {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }
        if (is_null($keyColumn)) {
            $keyColumn = $this->keyColumn;
        }
        $cc = $this->dblink->getColumnComma();
        $listQuery = SQL::$sel . $cc . $keyColumn . $cc . SQL::$frm . $tableName;
        return $this->dblink->queryToArray($listQuery);
    }

    /**
     * Vrací název aktuálně použivané SQL tabulky.
     *
     * @return string
     */
    public function getMyTable() {
        return $this->myTable;
    }

    /**
     * Nastaví aktuální pracovní tabulku pro SQL.
     *
     * @param string $myTable
     */
    public function setmyTable($myTable) {
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
    public function getSQLItemsCount($tableName = null) {
        if (is_null($tableName)) {
            $tableName = $this->myTable;
        }

        return $this->dblink->queryToValue(SQL::$sel . 'COUNT(' . $this->keyColumn . ') FROM ' . $tableName);
    }

    /**
     * Prohledá zadané slupečky.
     *
     * @param string $searchTerm
     * @param array  $columns
     */
    public function searchColumns($searchTerm, $columns) {
        $sTerm = $this->dblink->addSlashes($searchTerm);
        $conditons = [];
        foreach ($columns as $column) {
            $conditons[] = '`' . $column . '` LIKE \'%' . $sTerm . '%\'';
        }

        return $this->dblink->queryToArray(SQL::$sel . '* FROM ' . $this->myTable . SQL::$whr . implode(' OR ',
                                $conditons));
    }

}
