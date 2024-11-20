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
 * Virtual class for working with the database.
 *
 * @author Vitex <vitex@hippy.cz>
 */
abstract class SQL extends \Ease\Molecule
{
    /**
     * SQL operation result handle.
     *
     * @var resource
     */
    public $result;

    /**
     * SQL Handle.
     *
     * @var resource
     */
    public $sqlLink;

    /**
     * Connection status.
     */
    public bool $status = null;

    /**
     * Value of the last inserted AutoIncrement column.
     *
     * @var int unsigned
     */
    public int $lastInsertID = null;

    /**
     * Last executed SQL Query.
     */
    public string $lastQuery = '';

    /**
     * Number of affected or returned rows by $this->LastQuery.
     */
    public int $numRows = 0;

    /**
     * Array containing information about basic SQL connection parameters.
     */
    public array $report = ['LastMessage' => 'Please extend'];

    /**
     * Current database name.
     */
    public string $database = null;

    /**
     * Key column for SQL operations.
     */
    public string $keyColumn = '';

    /**
     * Name of the currently processed table.
     */
    public string $tableName = '';

    /**
     * Array containing the structure of the SQL table.
     */
    public array $tableStructure = [];

    /**
     * Array containing the results of the last SQL command.
     */
    public array $resultArray = [];

    /**
     * Auxiliary variable for data operations.
     */
    public array $data = null;

    /**
     * Last message received from the SQL server.
     */
    public string $lastMessage = null;

    /**
     * Connection settings properties.
     */
    public array $connectionSettings = [];

    /**
     * Indicator of connection settings - SET commands have been executed.
     */
    protected bool $connectAllreadyUP = false;

    /**
     * Last error number.
     */
    protected int $errorNumber;

    /**
     * Last error message.
     */
    private string $errorText;

    /**
     * General database object.
     *
     * @param mixed $options
     */
    public function __construct($options = [])
    {
        $this->setUp($options);
        $this->connect();
    }

    /**
     * Closes the database connection.
     */
    public function __destruct()
    {
        if (method_exists($this, 'close')) {
            $this->close();
        }
    }

    /**
     * Resets the last query when serializing.
     *
     * @return bool
     */
    public function __sleep()
    {
        $this->lastQuery = null;

        return [];
    }

    /**
     * SetUp Object to be ready for connect.
     *
     * @param array $options Object Options (company,url,user,password,evidence,
     *                       prefix,defaultUrlParams,debug)
     */
    public function setUp($options = []): void
    {
        $this->setupProperty($options, 'dbType', 'DB_CONNECTION'); // Laravel
        $this->setupProperty($options, 'dbType', 'DB_TYPE');       // Ease
        $this->setupProperty($options, 'server', 'DB_HOST');
        $this->setupProperty($options, 'username', 'DB_USERNAME');
        $this->setupProperty($options, 'password', 'DB_PASSWORD');
        $this->setupProperty($options, 'database', 'DB_DATABASE');
        $this->setupProperty($options, 'port', 'DB_PORT');
        $this->setupProperty($options, 'connectionSettings', 'DB_SETUP');
        $this->setupProperty($options, 'myTable');
    }

    /**
     * Connect to the database.
     */
    public function connect(): void
    {
        if (!$this->connectAllreadyUP) {
            if (isset($this->connectionSettings) && \is_array($this->connectionSettings) && \count($this->connectionSettings)) {
                foreach ($this->connectionSettings as $setName => $SetValue) {
                    if (\strlen($setName)) {
                        $this->exeQuery("SET {$setName} {$SetValue}");
                    }
                }

                $this->connectAllreadyUP = true;
            }
        }

        $this->status = true;
    }

    /**
     * Default database selector.
     *
     * @param string $dbName
     *
     * @return bool
     */
    public function selectDB($dbName = null)
    {
        if (null !== $dbName) {
            $this->database = $dbName;
        }

        return $this->database === $dbName;
    }

    /**
     * ID returned after INSERT.
     *
     * @return int
     */
    public function getInsertID()
    {
        return $this->lastInsertID;
    }

    /**
     * Test the possibility of connecting to the SQL server.
     */
    public function ping(bool $succes = false): bool
    {
        return $succes;
    }

    /**
     * Removes "dangerous" characters from the SQL query.
     *
     * @param string $queryRaw SQL Query
     *
     * @return string SQL Query
     */
    public function sanitizeQuery($queryRaw)
    {
        return trim($queryRaw);
    }

    /**
     * @param string $tableName
     */
    public function setTable($tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * Returns the number of rows returned or affected by the last SQL query.
     *
     * @return int number of rows
     */
    public function getNumRows()
    {
        return $this->numRows;
    }

    /**
     * Last executed query.
     *
     * @return int number of rows
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * Last generated ID.
     *
     * @return int ID
     */
    public function getlastInsertID()
    {
        return $this->lastInsertID;
    }

    /**
     * Returns the SQL error message.
     *
     * @return string
     */
    public function getLastError()
    {
        if ($this->errorText) {
            if (isset($this->errorNumber)) {
                return '#'.$this->errorNumber.': '.$this->errorText;
            }

            return $this->errorText;
        }
    }

    /**
     * Returns quotes for column names.
     *
     * @deprecated since version 1.2
     *
     * @return string
     */
    public function getColumnComma()
    {
        return '';
    }

    /**
     * Return connect status.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->status;
    }

    /**
     * From the $data array, creates a fragment of the SQL query for WHERE (the key column
     * $this->keyColumn is skipped if $key is false).
     *
     * @deprecated since version 1.0
     *
     * @param array $data
     * @param bool  $key
     *
     * @return string
     */
    public function arrayToSetQuery($data, $key = true)
    {
        $updates = '';

        foreach ($data as $column => $value) {
            if (!\strlen($column)) {
                continue;
            }

            if (($column === $this->keyColumn) && $key) {
                continue;
            }

            switch (\gettype($value)) {
                case 'integer':
                    $value = " {$value} ";

                    break;
                case 'float':
                case 'double':
                    $value = ' '.str_replace(',', '.', $value).' ';

                    break;
                case 'boolean':
                    if ($value) {
                        $value = ' TRUE ';
                    } else {
                        $value = ' FALSE ';
                    }

                    break;
                case 'NULL':
                    $value = ' null ';

                    break;
                case 'string':
                    if ($value !== 'NOW()') {
                        if (!strstr($value, "\\'")) {
                            $value = " '".str_replace("'", "\\'", $value)."' ";
                        } else {
                            $value = " '{$value}' ";
                        }
                    }

                    break;

                default:
                    $value = " '{$value}' ";
            }

            $updates .= ' '.$this->getColumnComma().$column.$this->getColumnComma()." = {$value},";
        }

        return substr($updates, 0, -1);
    }

    /**
     * Do when SQL error occurs.
     *
     * @param bool $ignoreErrors
     */
    public function logSqlError($ignoreErrors = false)
    {
        if (!$this->result && !$ignoreErrors) {
            $queryRaw = $this->lastQuery;
            $callerBackTrace = debug_backtrace();
            $callerBackTrace = $callerBackTrace[2];
            $caller = $callerBackTrace['function'].'()';

            if (isset($callerBackTrace['class'])) {
                $caller .= ' in '.$callerBackTrace['class'];
            }

            if (isset($callerBackTrace['object'])) {
                $caller .= ' ('.\get_class($callerBackTrace['object']).')';
            }

            return \Ease\Shared::logger()->addStatusObject(new \Ease\Logger\Message(
                'ExeQuery: #'.$this->errorNumber.': '.$this->errorText."\n".$queryRaw,
                'error',
                $caller,
            ));
        }
    }
}
