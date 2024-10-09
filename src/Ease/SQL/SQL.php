<?php

/**
 * Abstract database class.
 *
 * @deprecated since version 200
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2024 Vitex@vitexsoftware.cz (G)
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
    public $result = null;

    /**
     * SQL Handle.
     *
     * @var resource
     */
    public $sqlLink = null;

    /**
     * Connection status.
     *
     * @var bool
     */
    public $status = null;

    /**
     * Value of the last inserted AutoIncrement column.
     *
     * @var int unsigned
     */
    public $lastInsertID = null;

    /**
     * Last executed SQL Query.
     *
     * @var string
     */
    public $lastQuery = '';

    /**
     * Number of affected or returned rows by $this->LastQuery.
     *
     * @var int
     */
    public $numRows = 0;

    /**
     * Array containing information about basic SQL connection parameters.
     *
     * @var array
     */
    public $report = ['LastMessage' => 'Please extend'];

    /**
     * Current database name
     * @var string
     */
    public $database = null;

    /**
     * Key column for SQL operations.
     *
     * @var string
     */
    public $keyColumn = '';

    /**
     * Name of the currently processed table.
     *
     * @var string
     */
    public $tableName = '';

    /**
     * Array containing the structure of the SQL table.
     *
     * @var array
     */
    public $tableStructure = [];

    /**
     * Array containing the results of the last SQL command.
     *
     * @var array
     */
    public $resultArray = [];

    /**
     * Auxiliary variable for data operations.
     *
     * @var array
     */
    public $data = null;

    /**
     * Last message received from the SQL server.
     *
     * @var string
     */
    public $lastMessage = null;

    /**
     * Connection settings properties.
     *
     * @var array
     */
    public $connectionSettings = [];

    /**
     * Indicator of connection settings - SET commands have been executed.
     *
     * @var bool
     */
    protected $connectAllreadyUP = false;

    /**
     * Last error message
     * @var string
     */
    private $errorText;

    /**
     * Last error number
     * @var int
     */
    protected $errorNumber;

    /**
     * General database object.
     */
    public function __construct($options = [])
    {
        $this->setUp($options);
        $this->connect();
    }

    /**
     * SetUp Object to be ready for connect
     *
     * @param array $options Object Options (company,url,user,password,evidence,
     *                                       prefix,defaultUrlParams,debug)
     */
    public function setUp($options = [])
    {
        $this->setupProperty($options, 'dbType', 'DB_CONNECTION'); //Laravel
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
     * Connect to the database.
     */
    public function connect()
    {
        if (!$this->connectAllreadyUP) {
            if (isset($this->connectionSettings) && is_array($this->connectionSettings) && count($this->connectionSettings)) {
                foreach ($this->connectionSettings as $setName => $SetValue) {
                    if (strlen($setName)) {
                        $this->exeQuery("SET $setName $SetValue");
                    }
                }
                $this->connectAllreadyUP = true;
            }
        }
        $this->status = true;
    }

    /**
     * Default database selector
     *
     * @param string $dbName
     *
     * @return bool
     */
    public function selectDB($dbName = null)
    {
        if (!is_null($dbName)) {
            $this->database = $dbName;
        }

        return $this->database == $dbName;
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
        $sanitizedQuery = trim($queryRaw);
        return $sanitizedQuery;
    }

    /**
     *
     * @param string $tableName
     */
    public function setTable($tableName)
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
                return '#' . $this->errorNumber . ': ' . $this->errorText;
            } else {
                return $this->errorText;
            }
        } else {
            return;
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
     * Closes the database connection.
     */
    public function __destruct()
    {
        if (method_exists($this, 'close')) {
            $this->close();
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
            if (!strlen($column)) {
                continue;
            }
            if (($column == $this->keyColumn) && $key) {
                continue;
            }
            switch (gettype($value)) {
                case 'integer':
                    $value = " $value ";
                    break;
                case 'float':
                case 'double':
                    $value = ' ' . str_replace(',', '.', $value) . ' ';
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
                    if ($value != 'NOW()') {
                        if (!strstr($value, "\'")) {
                            $value = " '" . str_replace("'", "\'", $value) . "' ";
                        } else {
                            $value = " '$value' ";
                        }
                    }
                    break;
                default:
                    $value = " '$value' ";
            }

            $updates .= ' ' . $this->getColumnComma() . $column . $this->getColumnComma() . " = $value,";
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
            $caller = $callerBackTrace['function'] . '()';
            if (isset($callerBackTrace['class'])) {
                $caller .= ' in ' . $callerBackTrace['class'];
            }
            if (isset($callerBackTrace['object'])) {
                $caller .= ' (' . get_class($callerBackTrace['object']) . ')';
            }
            return \Ease\Shared::logger()->addStatusObject(new \Ease\Logger\Message(
                'ExeQuery: #' . $this->errorNumber . ': ' . $this->errorText . "\n" . $queryRaw,
                'error',
                $caller
            ));
        }
    }
}
