<?php

/**
 * Database Engine class
 * 
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2022 Vitex@hippy.cz (G)
 */

namespace Ease\Logger;

/**
 * Description of LogToSQL
 *
 * Use the phinx migration db/migrations/20200704143315_logger.php to create
 *
 * table structure:
 *
 * CREATE TABLE `log` (
 * `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 * `severity` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'message type',
 * `venue` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'message producer',
 * `message` text COLLATE utf8_czech_ci NOT NULL COMMENT 'main text',
 * `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 * PRIMARY KEY (`id`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
 *
 *
 * @author vitex
 */
class LogToSQL extends \Ease\SQL\Engine implements \Ease\Logger\Loggingable
{
    /**
     * Saves object instance (singleton...).
     */
    private static $instance = null;
    public string $myTable = 'log';
    public $applicationId = null;
    public $userId = null;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setUser(\Ease\User::singleton()->getUserID());
    }

    /**
     * When creating an object using the singleton function (it has the same parameters as
     * the constructor), only one instance of it will be used during the program run (the first one).
     *
     * @link http://docs.php.net/en/language.oop5.patterns.html Documentation and
     * example
     */
    public static function singleton()
    {
        if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class();
        }

        return self::$instance;
    }

    /**
     * ID of current user
     *
     * @param int $uid
     */
    public function setUser($uid)
    {
        $this->userId = $uid;
    }

    /**
     * Writes a message to the log.
     *
     * @param string $caller  name of the calling object
     * @param string $message message
     * @param string $type    type of message (success|info|error|warning|*)
     *
     * @return int|null was the report written?
     */
    public function addToLog($caller, $message, $type = 'message')
    {
        return $this->insertToSQL([
                    'venue' => self::venuize($caller),
                    'severity' => $type,
                    'message' => $message,
                    'user' => $this->userId
        ]);
    }

    /**
     * Prepare venue able to be saved into sql column
     *
     * @param mixed $caller
     */
    public static function venuize($caller)
    {
        switch (gettype($caller)) {
            case 'object':
                if (method_exists($caller, 'getObjectName')) {
                    $venue = $caller->getObjectName();
                } else {
                    $venue = get_class($caller);
                }
                break;
            case 'string':
            default:
                $venue = $caller;
                break;
        }
        return substr($venue, 254);
    }
}

