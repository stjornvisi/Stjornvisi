<?php

namespace Stjornvisi\Notify;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PDO;
use PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\Bootstrap;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

require_once 'MockQueueConnectionFactory.php';

abstract class AbstractTestCase extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;
    private $conn = null;
    /** @var MockQueueConnectionFactory */
    private $lastQueueConnectionFactory;

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
     * @param NotifyInterface|QueueConnectionAwareInterface|NotifyEventManagerAwareInterface $notifier
     * @param bool $throwOnCreateConnection
     */
    protected function prepareNotifier($notifier, $throwOnCreateConnection = false)
    {
        if ($notifier instanceof ServiceLocatorAwareInterface) {
            $notifier->setServiceLocator(Bootstrap::getServiceManager());
        }
        $notifier->setLogger($this->getNullLogger());
        $mock = $this->getQueueMock();
        if ($throwOnCreateConnection) {
            $mock->setThrowExceptionOnCreateConnection();
        }
        $notifier->setQueueConnectionFactory($mock);
        $this->lastQueueConnectionFactory = $mock;
    }

    /**
     * @param int $expectedCount
     */
    protected function checkNumChannelPublishes($expectedCount)
    {
        if ($channel = $this->getLastChannel()) {
            $actualCount = $channel->getTotalBasicPublish();
            $this->assertEquals($expectedCount, $actualCount);
        }
    }

    /**
     * @param string[] $expectedNames
     */
    protected function checkPublishedNames($expectedNames)
    {
        if ($channel = $this->getLastChannel()) {
            $actualNames = $channel->getNames();
            sort($actualNames);
            sort($expectedNames);
            $this->assertEquals($expectedNames, $actualNames);
        }
    }

    protected function checkChannelBody($contains, $num = 0)
    {
        $bodies = $this->getLastChannel()->getBodies();
        $this->assertArrayHasKey($num, $bodies);
        $this->assertContains($contains, $bodies[$num]);
    }

    protected function checkChannelSubject($contains, $num = 0)
    {
        $subjects = $this->getLastChannel()->getSubjects();
        $this->assertArrayHasKey($num, $subjects);
        $this->assertContains($contains, $subjects[$num]);
    }

    protected function checkGreeting($name, $num = 0)
    {
        $this->checkChannelBody("l(l) $name</p>", $num);
    }


    /**
     * @param bool $throwOnCreateConnection
     * @param string $class
     *
     * @return AbstractNotifier
     */
    protected function createNotifier($throwOnCreateConnection = false, $class = null)
    {
        if (!$class) {
            $class = $this->getNotifierClass();
        }
        /** @var AbstractNotifier $notifier */
        $notifier = Bootstrap::getServiceManager()->get($class);
        $this->prepareNotifier($notifier, $throwOnCreateConnection);
        return $notifier;
    }

    /**
     * @return string
     */
    abstract protected function getNotifierClass();

    /**
     * @return MockAMQPChannel
     */
    protected function getLastChannel()
    {
        if (!$this->lastQueueConnectionFactory) {
            return null;
        }
        $connection = $this->lastQueueConnectionFactory->getConnection();
        if (!$connection) {
            return null;
        }
        return $connection->getChannel();
    }

}
