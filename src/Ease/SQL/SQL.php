<?php

/**
 * Abstraktní databázová třída.
 *
 * @deprecated since version 200
 *
 * @author     Vitex <vitex@hippy.cz>
 * @copyright  2009-2023 Vitex@vitexsoftware.cz (G)
 */

namespace Ease\SQL;

/**
 * Virtuálni třída pro práci s databází.
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
     * Status připojení.
     *
     * @var bool
     */
    public $status = null;

    /**
     * Hodnota posledního voloženeho AutoIncrement sloupečku.
     *
     * @var int unsigned
     */
    public $lastInsertID = null;

    /**
     * Poslední vykonaná SQL Query.
     *
     * @var string
     */
    public $lastQuery = '';

    /**
     * Počet ovlivněných nebo vrácených řádek při $this->LastQuery.
     *
     * @var int
     */
    public $numRows = 0;

    /**
     * Pole obsahující informace o základních paramatrech SQL přiopojení.
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
     * Klíčový sloupeček pro SQL operace.
     *
     * @var string
     */
    public $keyColumn = '';

    /**
     * Název práve zpracovávané tabulky.
     *
     * @var string
     */
    public $tableName = '';

    /**
     * Pole obsahující strukturu SQL tabulky.
     *
     * @var array
     */
    public $tableStructure = [];

    /**
     * Pole obsahující výsledky posledního SQL příkazu.
     *
     * @var array
     */
    public $resultArray = [];

    /**
     * Pomocná proměnná pro datové operace.
     *
     * @var array
     */
    public $data = null;

    /**
     * Poslední zpráva obdžená od SQL serveru.
     *
     * @var string
     */
    public $lastMessage = null;

    /**
     * Nastavení vlastností přípojení.
     *
     * @var array
     */
    public $connectionSettings = [];

    /**
     * Indikátor nastavení připojení - byly vykonány SET příkazy.
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
     * Laste error number
     * @var int
     */
    protected $errorNumber;

    /**
     * Obecný objekt databáze.
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
     * Připojení k databázi.
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
     * Id vrácené po INSERTu.
     *
     * @return int
     */
    public function getInsertID()
    {
        return $this->lastInsertID;
    }

    /**
     * Otestuje moznost pripojeni k sql serveru.
     *
     * @param bool $succes vynucený výsledek
     *
     * @return $Success
     */
    public function ping($succes = null)
    {
        return $succes;
    }

    /**
     * Odstraní z SQL dotazu "nebezpečné" znaky.
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
     * Vrací počet řádků vrácených nebo ovlivněným posledním sql dotazem.
     *
     * @return int počet řádků
     */
    public function getNumRows()
    {
        return $this->numRows;
    }

    /**
     * Poslední vykonaný dotaz.
     *
     * @return int počet řádků
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * Poslední genrované ID.
     *
     * @return int ID
     */
    public function getlastInsertID()
    {
        return $this->lastInsertID;
    }

    /**
     * Vrací chybovou zprávu SQL.
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
     * Při serializaci vynuluje poslední Query.
     *
     * @return bool
     */
    public function __sleep()
    {
        $this->lastQuery = null;
        return [];
    }

    /**
     * Zavře databázové spojení.
     */
    public function __destruct()
    {
        if (method_exists($this, 'close')) {
            $this->close();
        }
    }

    /**
     * Vrací uvozovky pro označení sloupečků.
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
     * Return conect status.
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->status;
    }

    /**
     * z pole $data vytvori fragment SQL dotazu za WHERE (klicovy sloupec
     * $this->keyColumn je preskocen pokud neni $key false).
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
