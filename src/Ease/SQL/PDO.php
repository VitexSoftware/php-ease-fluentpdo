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
 * PDO Helper Class.
 *
 * @author Vitex <vitex@hippy.cz>
 */
class PDO extends SQL
{
    /**
     * DBO class instance.
     */
    public ?\PDO $pdo = null;

    /**
     * SQLLink result.
     */
    public \PDOStatement $result;

    /**
     * Connected state ?
     */
    public bool $status = false;

    /**
     * Last SQL query executed.
     */
    public string $lastQuery = '';

    /**
     * Last Query result length.
     */
    public int $numRows = 0;

    /**
     * Debug mode.
     */
    public bool $debug = false;

    /**
     * KeyColumn used row key.
     */
    public string $keyColumn = '';

    /**
     * Table name we used.
     */
    public string $myTable = '';
    public $charset = 'utf8';
    public $collate = 'utf8_czech_ci';

    /**
     * Allow Explain of each command to log logu ?
     *
     * @deprecated since version 1.4
     */
    public bool $explainMode = false;

    /**
     * Database Type: mysql|sqlite|sqlsrv etc ...
     */
    public string $dbType = '';

    /**
     * Saves object instance (singleton...).
     */
    private static $_instance;
    private string $errorText;

    /**
     * cleanup.
     */
    public function __destruct()
    {
        $this->pdo = null;
    }

    public function __wakeup(): void
    {
        $this->setUp();
    }

    /**
     * Pri vytvareni objektu pomoci funkce singleton (ma stejne parametry, jako konstruktor)
     * se bude v ramci behu programu pouzivat pouze jedna jeho instance (ta prvni).
     *
     * @see http://docs.php.net/en/language.oop5.patterns.html Dokumentace a priklad
     *
     * @param mixed $options
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
     * @return bool Operation success
     */
    public function setKeyColumn($column = null)
    {
        if (null !== $column) {
            $this->keyColumn = $column;
        }

        return $this->keyColumn === $column;
    }

    /**
     * Set Table we work with.
     */
    public function setTableName(string $tablename): bool
    {
        $this->myTable = $tablename;

        return true;
    }

    /**
     * Escapes special characters in a string for use in an SQL statement.
     *
     * @deprecated since version 0.1
     *
     * @param string $text
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
     * @param null|mixed $dbName
     */
    public function selectDB($dbName = null): bool
    {
        $change = false;
        parent::selectDB($dbName);

        if (method_exists($this->pdo, 'select_db')) {
            $change = $this->pdo->select_db($dbName);

            if ($change) {
                $this->database = $dbName;
            } else {
                if (property_exists($this->pdo, 'error')) {
                    $this->errorText = $this->pdo->error;
                }

                if (property_exists($this->pdo, 'errno')) {
                    $this->errorNumber = $this->pdo->errno;
                }
            }
        }

        return $change;
    }

    /**
     * Poslední genrované ID.
     *
     * @param null|mixed $column
     *
     * @return int ID
     */
    public function getlastInsertID($column = null)
    {
        switch ($this->dbType) {
            case 'pgsql':
                if (null === $column) {
                    $column = $this->myTable.'_'.$this->keyColumn.'_seq';
                } else {
                    $column = $this->myTable.'_'.$column.'_seq';
                }

                break;

            default:
                break;
        }

        return $this->pdo->lastInsertId($column);
    }

    /**
     * Ukončí připojení k databázi.
     */
    public function close()
    {
        return $this->pdo = null;
    }
}
