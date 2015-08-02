<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 29/09/14
 * Time: 11:53
 */

namespace Stjornvisi\Notify;

use PHPUnit_Extensions_Database_TestCase;

use Stjornvisi\Bootstrap;
use Stjornvisi\ArrayDataSet;

use Monolog\Handler\NullHandler;
use Monolog\Logger;

use \PDO;

class AttendTest extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;

    private $conn = null;

    public function testEverythingOk()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()
                            ->shouldReceive('queue_declare', 'basic_publish', 'close')
                            ->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();

        $notify = new Attend();
        $notify->setDateStore($this->getDatabaseConnectionValues());
        $notify->setQueueConnectionFactory($mock);
        $notify->setLogger($this->getNullLogger());
        $notify->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notify->send();
    }

    public function testEverythingWithGuestUser()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()
                            ->shouldReceive('queue_declare', 'basic_publish', 'close')
                            ->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();

        $notify = new Attend();
        $notify->setDateStore($this->getDatabaseConnectionValues());
        $notify->setQueueConnectionFactory($mock);
        $notify->setLogger($this->getNullLogger());
        $notify->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => (object)['name'=>'n1', 'email'=>'e@e.is'],
                'type' => true
            ]
        ]);
        $notify->send();
    }

    /**
     * @expectedExceptionMessage No recipient provided
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testNoUserProvided()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()
                            ->shouldReceive('queue_declare', 'basic_publish', 'close')
                            ->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();

        $notify = new Attend();
        $notify->setDateStore($this->getDatabaseConnectionValues());
        $notify->setQueueConnectionFactory($mock);
        $notify->setLogger($this->getNullLogger());
        $notify->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => null,
                'type' => true
            ]
        ]);
        $notify->send();
    }

    /**
     * @expectedExceptionMessage User [100] not found
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testUserNotFound()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()
                            ->shouldReceive('queue_declare', 'basic_publish', 'close')
                            ->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();

        $notify = new Attend();
        $notify->setDateStore($this->getDatabaseConnectionValues());
        $notify->setQueueConnectionFactory($mock);
        $notify->setLogger($this->getNullLogger());
        $notify->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => 100,
                'type' => true
            ]
        ]);
        $notify->send();
    }


    /**
     * @expectedExceptionMessage Event [100] not found
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testEventNotFound()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()
                            ->shouldReceive('queue_declare', 'basic_publish', 'close')
                            ->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();

        $notify = new Attend();
        $notify->setDateStore($this->getDatabaseConnectionValues());
        $notify->setQueueConnectionFactory($mock);
        $notify->setLogger($this->getNullLogger());
        $notify->setData((object)[
            'data' => (object)[
                'event_id' => 100,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notify->send();
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testConnectionException()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')->andThrow('\PhpAmqpLib\Exception\AMQPRuntimeException')
            ->getMock();

        $notify = new Attend();
        $notify->setDateStore($this->getDatabaseConnectionValues());
        $notify->setQueueConnectionFactory($mock);
        $notify->setLogger($this->getNullLogger());
        $notify->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notify->send();
    }

    /**
     * @expectedException \PDOException
     */
    public function testEverythingNotOk()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()
                            ->shouldReceive('queue_declare', 'basic_publish', 'close')
                            ->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();

        $notify = new Attend();
        $notify->setDateStore(array_merge($this->getDatabaseConnectionValues(), ['user'=>'hvadagaurertetta']));
        $notify->setQueueConnectionFactory($mock);
        $notify->setLogger($this->getNullLogger());
        $notify->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notify->send();
    }

    /**
     *
     */
    protected function setUp()
    {
        Bootstrap::getServiceManager();
        $conn=$this->getConnection();
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
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'User' => [
                ['id'=>1,'name'=>'einar','passwd'=>'1234','created_date'=>date('Y-m-d'),'modified_date'=>date('Y-m-d')],
            ],
            'Event' => [
                ['id'=>1,'subject'=>'s1']
            ],
        ]);
    }

    /**
     * Provide some connection values for the
     * database.
     *
     * @return array
     */
    private function getDatabaseConnectionValues()
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

    /**
     * @return Logger
     */
    private function getNullLogger()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        return $logger;
    }
}
