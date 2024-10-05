<?php

/**
 * Database Engine class
 * 
 * Author: Vítězslav Dvořák <info@vitexsoftware.cz>
 * Copyright 2018-2024 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

/**
 * Description of Engine
 * 
 * Author: Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class Engine extends \Ease\Brick
{
    use Orm;

    /**
     * Default table in SQL (part of identity).
     *
     * @var string
     */
    public string $myTable = '';

    /**
     * Record create time column
     * @var string
     */
    public ?string $createColumn = null;

    /**
     * Database object
     *
     * @param mixed $identifier
     * @param array $options  'autoload'=>false prevent initial autoloading, keyColumn,myTable,createColumn,lastModifiedColumn,nameColumn
     */
    public function __construct($identifier = null, $options = [])
    {
        parent::__construct($identifier, $options);
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
    public function useIdentifier($identifier): void
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
     * Load record using identifier
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
     * @param \Ease\SQL\Engine $identifier
     *
     * @return string id|name|values|reuse|unknown
     */
    public function howToProcess($identifier)
    {
        $recognizedAs = 'unknown';
        switch (gettype($identifier)) {
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
                if ($identifier instanceof \Ease\SQL\Engine) {
                    $recognizedAs = 'reuse';
                }
                break;
            default:
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
     * Obtain record name if $this->nameColumn is set
     *
     * @return string
     */
    public function getRecordName()
    {
        return empty($this->nameColumn) ? null : $this->getDataValue($this->nameColumn);
    }

    /**
     * Returns the name of the currently used SQL table.
     *
     * @return string
     */
    public function getMyTable()
    {
        return $this->myTable;
    }

    /**
     * Sets the current working table for SQL.
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
        $conditions = [];
        foreach ($columns as $column) {
            $conditions[] = '`' . $column . '` LIKE \'%' . addslashes($searchTerm) . '%\'';
        }

        return $this->listingQuery()->where($conditions);
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
        return empty($data) ? [] : $data;
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

    /**
     * Set/override object properties
     *
     * @param array $properties
     */
    public function setProperties(array $properties = []): void
    {
        $this->setupProperty($properties, 'myTable');
        $this->setupProperty($properties, 'keyColumn');
        $this->setupProperty($properties, 'nameColumn');
        $this->setupProperty($properties, 'createColumn');
        $this->setupProperty($properties, 'lastModifiedColumn');
    }
}
