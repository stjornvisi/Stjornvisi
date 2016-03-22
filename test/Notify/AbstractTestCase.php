<?php

namespace Stjornvisi\Notify;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PDO;
use PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\Bootstrap;
use Stjornvisi\Lib\QueueConnectionAwareInterface;

require_once 'MockQueueConnectionFactory.php';

abstract class AbstractTestCase extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;
    private $conn = null;

    /**
     * Setup database.
     */
    protected function setUp()
    {
        Bootstrap::getServiceManager();
        $conn = $this->getConnection();
        $conn->getConnection()->query("set foreign_key_checks=0");
        parent::setUp();
        $conn->getConnection()->query("set foreign_key_checks=1");
    }

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {

        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO(
                    $GLOBALS['DB_DSN'],
                    $GLOBALS['DB_USER'],
                    $GLOBALS['DB_PASSWD'],
                    [
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    ]
                );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo);
        }
        return $this->conn;
    }

    /**
     * Provide some connection values for the
     * database.
     *
     * @return array
     */
    public function getDatabaseConnectionValues()
    {
        return [
            'dns' => $GLOBALS['DB_DSN'],
            'user' => $GLOBALS['DB_USER'],
            'password' => $GLOBALS['DB_PASSWD'],
            'options' => [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
            ]
        ];
    }

    protected function getQueueMock()
    {
        return new MockQueueConnectionFactory();
    }

    /**
     * @return Logger
     */
    protected function getNullLogger()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        return $logger;
    }

    /**
     * @param NotifyInterface|QueueConnectionAwareInterface|DataStoreInterface|NotifyEventManagerAwareInterface $notifier
     * @param bool $throwOnCreateConnection
     */
    protected function prepareNotifier($notifier, $throwOnCreateConnection = false)
    {
        if ($notifier instanceof DataStoreInterface) {
            $notifier->setDateStore($this->getDatabaseConnectionValues());
        }
        $notifier->setLogger($this->getNullLogger());
        $mock = $this->getQueueMock();
        if ($throwOnCreateConnection) {
            $mock->setThrowExceptionOnCreateConnection();
        }
        $notifier->setQueueConnectionFactory($mock);
    }
}
