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

        if (!is_null($identifier)) {
            if (array_key_exists('autoload', $options) && ($options['autoload'] === false)) {
                if (is_array($identifier)) {
                    $this->takeData($identifier);
                } else {
                    $this->setMyKey($identifier);
                }
            } else {
                $this->loadFromSQL($identifier);
            }
        }
    }

    /**
     * Obtain record name id $this->nameColumn is set
     * @return string
     */
    public function getRecordName()
    {
        return empty($this->nameColumn) ? $this->getDataValue($this->nameColumn)
                : null;
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
            $conditons[] = '`'.$column.'` LIKE \'%'. addslashes($sTerm).'%\'';
        }

        return $this->listingQuery()->where($conditons);
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
