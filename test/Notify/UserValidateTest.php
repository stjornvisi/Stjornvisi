<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 16/02/15
 * Time: 12:21
 */

namespace Stjornvisi\Notify;

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\DataHelper;
use Stjornvisi\PDOMock;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Stjornvisi\Bootstrap;

class UserValidateTest extends \PHPUnit_Extensions_Database_TestCase
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
        $notifier = new UserValidate();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
                'facebook' => 'akdjfghseiurg'
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\UserValidate', $notifier->send());
    }

    /**
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testConnectionException()
    {

        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')->andThrow('\PhpAmqpLib\Exception\AMQPRuntimeException')
            ->getMock();

        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new UserValidate();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
                'facebook' => 'akdjfghseiurg'
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\UserValidate', $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage User [100] not found
     */
    public function testUserNotFound()
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()
                            ->shouldReceive(['queue_declare', 'basic_publish', 'close'])
                            ->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();


        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        $notifier = new UserValidate();
        $notifier->setDateStore($this->getDatabaseConnectionValues());
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => true,
                'recipient' => 1,
                'body' => 'nothing',
                'subject' => '',
                'group_id' => 1,
                'user_id' => 100,
                'facebook' => 'akdjfghseiurg'
            ]
        ]);
        $notifier->setLogger($logger);
        $notifier->setQueueConnectionFactory($mock);

        $this->assertInstanceOf('\Stjornvisi\Notify\UserValidate', $notifier->send());
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
            'User' => [
                ['id'=>1, 'name'=>'n1', 'passwd'=>md5(rand(0, 9)), 'email'=>'e@mail.com', 'title'=>'t1', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>2, 'name'=>'n1', 'passwd'=>md5(rand(0, 9)), 'email'=>'e@mail2.com', 'title'=>'t1', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
            ],
            'Group' => [
                DataHelper::newGroup(1),
                DataHelper::newGroup(2),
                DataHelper::newGroup(3),
                DataHelper::newGroup(4),
            ],
            'Event' => [
                ['id'=>1, 'subject'=>'01', 'body'=>'01',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-4 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>2, 'subject'=>'02', 'body'=>'02',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-3 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>3, 'subject'=>'03', 'body'=>'03',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-2 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>4, 'subject'=>'04', 'body'=>'04',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-1 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>5, 'subject'=>'05', 'body'=>'05',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d'),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>6, 'subject'=>'06', 'body'=>'06',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d', strtotime('+1 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>7, 'subject'=>'07', 'body'=>'07',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d', strtotime('+2 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>8, 'subject'=>'08', 'body'=>'08',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d', strtotime('+3 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>9, 'subject'=>'09', 'body'=>'09',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d', strtotime('+4 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
            ],
            'Group_has_Event' => [
                ['event_id'=>2, 'group_id'=>1,'primary'=>0],
                ['event_id'=>2, 'group_id'=>2,'primary'=>0],
                ['event_id'=>2, 'group_id'=>3,'primary'=>0],

                ['event_id'=>3, 'group_id'=>2,'primary'=>0],
                ['event_id'=>4, 'group_id'=>null,'primary'=>0],
            ],
            'Event_has_Guest' => [
                ['event_id'=>1,'name'=>'n1','email'=>'e@a.is','register_time'=>date('Y-m-d H:i:s')],
                ['event_id'=>1,'name'=>'n2','email'=>'b@a.is','register_time'=>date('Y-m-d H:i:s')],
                ['event_id'=>9,'name'=>'n1','email'=>'e@a.is','register_time'=>date('Y-m-d H:i:s')],
                ['event_id'=>9,'name'=>'n2','email'=>'b@a.is','register_time'=>date('Y-m-d H:i:s')],
            ],
            'Event_has_User' => [
                ['event_id' => 1, 'user_id'=>1,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],
                ['event_id' => 9, 'user_id'=>1,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],

                ['event_id' => 2, 'user_id'=>2,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],
            ],
            'EventMedia' => [
                ['id' => 1, 'name' => 'hundur1', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 2, 'name' => 'hundur2', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 3, 'name' => 'hundur3', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 4, 'name' => 'hundur4', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 5, 'name' => 'hundur5', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 6, 'name' => 'hundur6', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 7, 'name' => 'hundur7', 'event_id' => 4, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 8, 'name' => 'hundur8', 'event_id' => 4, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
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
