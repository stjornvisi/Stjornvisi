<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 8/04/15
 * Time: 6:28 AM
 */

namespace Stjornvisi\Notify;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\PDOMock;
use Stjornvisi\Bootstrap;

class AllTest extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;

    private $conn = null;

    /**
     * Everything should run.
     *
     * @throws NotifyException
     */
    public function testEverythingWorks()
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
        $notifier = new All();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
           'data' => (object)[
               'sender_id' => 1,
               'test' => true,
               'recipients' => 'allir',
               'body' => 'nothing',
               'subject' => ''
           ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\All', $notifier->send());

    }

    /**
     * Everything should run.
     *
     * @throws NotifyException
     */
    public function testGetAllUsersNotTest()
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
        $notifier = new All();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => false,
                'recipients' => 'allir',
                'body' => 'nothing',
                'subject' => ''
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\All', $notifier->send());

    }

    /**
     * @throws NotifyException
     * @expectedException \PDOException
     */
    public function testNotProvidingRightCredentialsForDatabase()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()->shouldReceive('queue_declare', 'basic_publish', 'close')->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new All();
        $notifier->setDateStore(
            array_merge($this->getDatabaseConnectionValues(), ['user'=>'tettaerskoekkinotandi'])
        );
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => true,
                'recipients' => 'allir',
                'subject' => '',
                'body' => ''
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\All', $notifier->send());

    }

    /**
     * Missing properties in the data object provided
     * to an instance of the Notifier.
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage Missing data:subject
     */
    public function testNotPassingIntRequiredDataPropertiesException()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()->shouldReceive('queue_declare', 'basic_publish', 'close')->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new All();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => true,
                'recipients' => 'allir'
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\All', $notifier->send());

    }

    /**
     * RabbitMQ can't connect to server.
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testQueueThrowingConnectionException()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')->andThrow('\PhpAmqpLib\Exception\AMQPRuntimeException')
            ->getMock();

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new All();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'subject' => '',
                'sender_id' => 1,
                'test' => true,
                'body' => '',
                'recipients' => 'allir'
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\All', $notifier->send());

    }

    /**
     * Setup database.
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
            'Company' => [
                ['id'=>1,'name'=>'n1','ssn'=>'1234567890','address'=>'a1','zip'=>'1','website'=>null,'number_of_employees'=>'','business_type'=>'hf','safe_name'=>'s1','created'=>date('Y-m-d H:i:s')],
                ['id'=>2,'name'=>'n2','ssn'=>'2234567890','address'=>'b1','zip'=>'2','website'=>null,'number_of_employees'=>'','business_type'=>'sf','safe_name'=>'s2','created'=>date('Y-m-d H:i:s')],
                ['id'=>3,'name'=>'n3','ssn'=>'3234567890','address'=>'c1','zip'=>'3','website'=>null,'number_of_employees'=>'','business_type'=>'ohf','safe_name'=>'s3','created'=>date('Y-m-d H:i:s')],
                ['id'=>4,'name'=>'n4','ssn'=>'4234567890','address'=>'d1','zip'=>'4','website'=>null,'number_of_employees'=>'','business_type'=>'hf','safe_name'=>'s4','created'=>date('Y-m-d H:i:s')],
            ],
            'User' => [
                ['id'=>1, 'name'=>'', 'passwd'=>'', 'email'=>'one@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>1],
                ['id'=>2, 'name'=>'', 'passwd'=>'', 'email'=>'two@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>3, 'name'=>'', 'passwd'=>'', 'email'=>'three@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
            ],
            'Company_has_User' => [
                ['user_id'=>1,'company_id'=>1,'key_user'=>0],
                ['user_id'=>2,'company_id'=>1,'key_user'=>0],
                ['user_id'=>2,'company_id'=>2,'key_user'=>0],
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
