<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 30/09/14
 * Time: 19:26
 */

namespace Stjornvisi\Notify;

use PHPUnit_Extensions_Database_TestCase;
use \PDO;

use Monolog\Handler\NullHandler;
use Monolog\Logger;

use Stjornvisi\Notify\Event;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\Bootstrap;

class EventTest extends PHPUnit_Extensions_Database_TestCase
{
	static private $pdo = null;

	private $conn = null;

    public function testOk()
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

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new Event();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
                'recipients' => 1,
                'test' => false,
                'body' => '',
                'subject' => '',
                'event_id' => 1,
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\Event', $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage Event [100] not found
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

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new Event();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 100,
                'user_id' => 1,
                'recipients' => 'allir',
                'test' => false,
                'body' => '',
                'subject' => ''
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\Event', $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage Sender not found
     */
    public function testUserNotFoundInTestMode()
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

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new Event();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'user_id' => 100,
                'recipients' => 'allir',
                'test' => true,
                'body' => '',
                'subject' => ''
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\Event', $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testQueueConnectionException()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')->andThrow('\PhpAmqpLib\Exception\AMQPRuntimeException')
            ->getMock();

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new Event();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'user_id' => 1,
                'recipients' => 'allir',
                'test' => true,
                'body' => '',
                'subject' => ''
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\Event', $notifier->send());
    }

    /**
     *
     */
    public function testUserFoundInTestMode()
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

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new Event();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'user_id' => 1,
                'recipients' => 1,
                'test' => true,
                'body' => '',
                'subject' => ''
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\Event', $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage No users found for event notification
     */
    public function testEveryBodyNotOk()
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

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new Event();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'user_id' => 1,
                'recipients' => 'allir',
                'test' => false,
                'body' => '',
                'subject' => ''
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\Event', $notifier->send());
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
				['id'=>1,'subject'=>'s1'],
				['id'=>2,'subject'=>'s1'],
			],
			'Group' => [
				['id'=>1, 'name'=>'n1', 'name_short' => 'ns1','url'=>'u1'],
			],
			'Group_has_Event' => [
				['event_id'=>1, 'group_id'=>1],
				['event_id'=>1, 'group_id'=>null],
			],
		]);
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
}
