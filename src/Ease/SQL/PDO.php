<?php

/**
 * Obsluha SQL PDO.
 *
 * @author    Vitex <vitex@hippy.cz>
 * @copyright 2015-2023 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

/**
 * PDO Helper Class
 *
 * @author Vitex <vitex@hippy.cz>
 */
class PDO extends SQL
{

    /**
     * DBO class instance.
     *
     * @var \PDO|null
     */
    public $pdo = null;

    /**
     * SQLLink result.
     *
     * @var \PDOStatement
     */
    public $result = null;

    /**
     * Connected state ?
     * @var bool
     */
    public $status = false;

    /**
     * Last SQL query executed
     * @var string
     */
    public $lastQuery = '';

    /**
     * Last Query result length
     * @var int
     */
    public $numRows = 0;

    /**
     * Debug mode
     * @var boolean
     */
    public $debug = false;

    /**
     * KeyColumn used for postgresql insert id.
     *
     * @var string
     */
    public $keyColumn = null;

    /**
     * Table used for postgresql insert id.
     *
     * @var string
     */
    public $myTable = null;
    public $data = null;
    public $charset = 'utf8';
    public $collate = 'utf8_czech_ci';

    /**
     * Povolit Explain každého dotazu do logu ?
     *
     * @var bool
     */
    public $explainMode = false;

    /**
     * Saves obejct instace (singleton...).
     */
    private static $_instance = null;

    /**
     * Database Type: mysql|sqlite|etc ...
     *
     * @var string
     */
    public $dbType = null;

    /**
     * 
     * @var string
     */
    private $errorText;

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako konstruktor)
     * se bude v ramci behu programu pouzivat pouze jedna jeho instance (ta prvni).
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     */
    public static function singleton($options = [])
    {
        if (!isset(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class($options);
        }

        return self::$_instance;
    }

    /**
     * Set KeyColumn used for PGSQL indertid.
     *
     * @param string $column
     * 
     * @return boolean Operation success
     */
    public function setKeyColumn($column = null)
    {
        if (!is_null($column)) {
            $this->keyColumn = $column;
        }
        return $this->keyColumn == $column;
    }

    /**
     * Set Table used for PGSQL indertid.
     *
     * @param string $tablename
     */
    public function setTableName($tablename = null)
    {
        if (!empty($tablename)) {
            $this->myTable = $tablename;
        }
    }

    /**
     * Escapes special characters in a string for use in an SQL statement.
     *
     * @param string $text
     * 
     * @deprecated since version 0.1
     *
     * @return string
     */
    public function addSlashes($text)
    {
        if (isset($this->pdo) && method_exists($this->pdo, 'real_escape_string')) {
            $slashed = $this->pdo->real_escape_string($text);
        } else {
            $slashed = addslashes($text);
        }

        return $slashed;
    }

    /**
     * Změní aktuálně použitou databázi.
     *
     * @param string $dbName
     *
     * @return bool
     */
    public function selectDB($dbName = null)
    {
        $change = false;
        parent::selectDB($dbName);
        if (method_exists($this->pdo, 'select_db')) {
            $change = $this->pdo->select_db($dbName);
            if ($change) {
                $this->database = $dbName;
            } else {
                $this->errorText = $this->pdo->error;
                $this->errorNumber = $this->pdo->errno;
            }
        }
        return $change;
    }

    /**
     * Poslední genrované ID.
     *
     * @return int ID
     */
    public function getlastInsertID($column = null)
    {
        switch ($this->dbType) {
            case 'pgsql':
                if (is_null($column)) {
                    $column = $this->myTable . '_' . $this->keyColumn . '_seq';
                } else {
                    $column = $this->myTable . '_' . $column . '_seq';
                }
                break;
            default:
                break;
        }

        return $this->pdo->lastInsertId($column);
    }

    /**
     * Ukončí připojení k databázi.
     *
     * @return null
     */
    public function close()
    {
        return $this->pdo = null;
    }

    /**
     * Virtuální funkce.
     */
    public function __destruct()
    {
        unset($this->pdo);
        unset($this->result);
    }

    /**
     * You cannot serialize or unserialize PDO instance.
     *
     * @return array fields to serialize
     */
    public function __sleep()
    {
        return parent::__sleep();
    }

    /**
     * 
     */
    public function __wakeup()
    {
        $this->setUp();
    }
}