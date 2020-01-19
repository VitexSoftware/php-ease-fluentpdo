<?php
/**
 * Object Relation Model Trait
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018 Vitex@hippy.cz (G)
 */

namespace Ease\SQL;

/**
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
trait Orm
{
    /**
     * IP serveru.
     *
     * @var string
     */
    public $server = null;

    /**
     * DB Login.
     *
     * @var string
     */
    public $username = null;

    /**
     * DB heslo.
     *
     * @var string
     */
    public $password = null;

    /**
     * Database to connect by default.
     *
     * @var string
     */
    public $database = null;

    /**
     * Database port.
     *
     * @var string
     */
    public $port = null;

    /**
     * Type of used database.
     *
     * @var string mysql|pgsql|..
     */
    public $dbType;

    /**
     * Nastavení vlastností přípojení.
     *
     * @var array
     */
    public $connectionSettings = [];

    /**
     * Objekt pro práci s SQL.
     *
     * @var PDO
     */
    public $pdo = null;

    /**
     *
     * @var Fluent 
     */
    public $fluent = null;

    /**
     * Poslední Chybová zpráva obdržená od SQL serveru.
     *
     * @var array
     */
    public $errorInfo = [];

    /**
     * Kod SQL chyby.
     *
     * @var int
     */
    public $errorNumber = null;

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
     * Perform connect to database.
     *
     * @return \PDO SQL connector
     */
    public function pdoConnect()
    {
        $result = false;
        if (is_null($this->dbType)) {
            $result = null;
        } else {
            switch ($this->dbType) {
                case 'mysql':
                    $result = new \PDO($this->dbType.':dbname='.$this->database.';host='.$this->server.';port='.$this->port.';charset=utf8',
                        $this->username, $this->password,
                        [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'']);
                    break;
                case 'pgsql':
                    $result = new \PDO($this->dbType.':dbname='.$this->database.';host='.$this->server.';port='.$this->port,
                        $this->username, $this->password);
                    if (is_object($result)) {
                        $result->exec("SET NAMES 'UTF-8'");
                    }
                    break;
                case 'sqlsrv': // https://www.php.net/manual/en/ref.pdo-sqlsrv.connection.php
                    $result = new \PDO($this->dbType.':Server='.$this->server. (isset($this->port) ? ','.$this->port : '') .  ';Database='.$this->database,
                        $this->username, $this->password);
                    break;

                default:
                    throw new \Ease\Exception(_('Unimplemented Database type').': '.$this->dbType);
                    break;
            }

            if ($result instanceof \PDO) {
                $this->errorNumber = $result->errorCode();
                $this->errorInfo   = $result->errorInfo();

                if (($this->errorNumber != '00000') && ($this->errorNumber != '01000')) { // SQL_SUCCESS_WITH_INFO
                    $this->addStatusMessage('Connect: error #'.$this->errorNumer.' '.$this->errorInfo,
                        'error');

                    $result = false;
                } else {
                    if (!empty($this->connectionSettings))
                            foreach ($this->connectionSettings as $setName => $SetValue) {
                            if (strlen($setName)) {
                                $this->getPdo->exec("SET $setName $SetValue");
                            }
                        }
                }
            }
        }

        return $result;
    }

    /**
     * (init &) Get PDO instance
     * 
     * @return \PDO
     */
    public function getPdo()
    {
        if (!$this->pdo instanceof \PDO) {
            $this->pdo = $this->pdoConnect();
        }
        return $this->pdo;
    }

    /**
     * SQL Builder
     * 
     * @return \Envms\FluentPDO
     */
    public function getFluentPDO()
    {
        if (!$this->fluent instanceof \Envms\FluentPDO) {
            $this->fluent = new \Envms\FluentPDO\Query($this->getPdo());
        }
        return $this->fluent;
    }

    /**
     * 
     * @return string
     */
    public function getMyTable()
    {
        return $this->myTable;
    }

    /**
     * 
     * @param string $tablename
     */
    public function setMyTable($tablename)
    {
        $this->myTable = $tablename;
    }
}
