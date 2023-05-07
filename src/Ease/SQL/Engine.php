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
     * Rown name column
     * @var string
     */
    public $nameColumn = null;

    /**
     * Database object
     * 
     * @param mixed $identifier
     * @param array $options  'autoload'=>false prevent inial autoloading, keyColumn,myTable,createColumn,lastModifiedColumn,nameColumn
     */
    public function __construct($identifier = null, $options = [])
    {
        $this->setupProperty($options, 'myTable');
        $this->setupProperty($options, 'keyColumn');
        $this->setupProperty($options, 'nameColumn');
        $this->setupProperty($options, 'createColumn');
        $this->setupProperty($options, 'lastModifiedColumn');
        $this->setUp($options);
        if (array_key_exists('autoload', $options) && ($options['autoload'] === true)) {
            $this->loadIdentifier($identifier);
        } else {
            $this->useIdentifier($identifier);
        }
    }

    /**
     * Use Given value as identifier
     * 
     * @param mixed $identifier
     */
    public function useIdentifier($identifier)
    {
        switch ($this->howToProcess($identifier)) {
            case 'values':
                $this->takeData($identifier);
                break;
            case 'reuse':
                $this->takeData($identifier->getData());
                break;
            case 'name':
                $this->setDataValue($this->nameColumn, $identifier);
                break;
            case 'id':
                $this->setMyKey($identifier);
                break;
            default:
                break;
        }
    }

    /**
     * Load record usinf identifier
     * 
     * @param mixed $identifier
     */
    public function loadIdentifier($identifier)
    {
        switch ($this->howToProcess($identifier)) {
            case 'values':
                $this->loadFromSQL($identifier);
                break;
            case 'reuse':
                $this->takeData($identifier->getData());
                break;
            case 'name':
                $this->loadFromSQL([$this->nameColumn => $identifier]);
                break;
            case 'id':
                $this->loadFromSQL($identifier);
                break;
            default:
                break;
        }
    }

    /**
     * 
     * @param \Ease\SQL\Engine $identifer
     * 
     * @return string id|name|values|reuse|unknown
     */
    public function howToProcess($identifer)
    {
        $recognizedAs = 'unknown';
        switch (gettype($identifer)) {
            case "integer":
            case "double":
                if ($this->getKeyColumn()) {
                    $recognizedAs = 'id';
                }
                break;
            case "string":
                if (!empty($this->nameColumn)) {
                    $recognizedAs = 'name';
                }
                break;
            case "array":
                $recognizedAs = 'values';
                break;
            case "object":
                if ($identifer instanceof \Ease\SQL\Engine) {
                    $recognizedAs = 'reuse';
                }
                break;
            default :
            case "boolean":
            case "NULL":
                $recognizedAs = 'unknown';
                break;
        }
        return $recognizedAs;
    }

    /**
     * Prove that record is present in DB
     * 
     * @param string|int|array $identifier
     * 
     * @return boolean Record was found ?
     */
    public function recordExist($identifier = null)
    {
        return $this->listingQuery()->where(is_null($identifier) ? [$this->getKeyColumn() => $this->getMyKey()] : $identifier)->count() != 0;
    }

    /**
     * Obtain record name id $this->nameColumn is set
     * 
     * @return string
     */
    public function getRecordName()
    {
        return empty($this->nameColumn) ? null : $this->getDataValue($this->nameColumn);
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
    }

    /**
     * Search columns for given value.
     *
     * @param string $searchTerm
     * @param array  $columns
     */
    public function searchColumns($searchTerm, $columns)
    {
        $conditons = [];
        foreach ($columns as $column) {
            $conditons[] = '`' . $column . '` LIKE \'%' . addslashes($searchTerm) . '%\'';
        }

        return $this->listingQuery()->where($conditons);
    }

    /**
     * Always return array
     * 
     * @param \Envms\FluentPDO\Queries\Select $query
     * 
     * @return array
     */
    public static function fixIterator($query)
    {
        $data = $query->execute();
        return $data ? $data : [];
    }

    /**
     * Get All records
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->listingQuery()->fetchAll();
    }
}
