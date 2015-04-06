<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/9/14
 * Time: 3:10 PM
 */

namespace Stjornvisi\Service;

use Stjornvisi\PDOMock;
use Stjornvisi\Service\Event;
use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\Bootstrap;

/**
 * Class EventGetTest
 * @package Stjornvisi\Service
 */
class EventGetTest extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;

    private $conn = null;

	private $config;

	/**
	 * @var Event
	 */
	private $service;


	public function testGetResourceNotFound()
	{
		$this->assertFalse($this->service->get(1000));
	}

	public function testGet()
	{
		$event = $this->service->get(1);

		$this->assertInternalType('int', $event->id);
		$this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_time);
		$this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_end);
		$this->assertInstanceOf('\DateTime', $event->event_date);
		$this->assertNull($event->capacity);
		$this->assertNull($event->avatar);

		$event = $this->service->get(2);

		$this->assertInternalType('int', $event->id);
		$this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_time);
		$this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_end);
		$this->assertInstanceOf('\DateTime', $event->event_date);
		$this->assertNull($event->capacity);
		$this->assertNull($event->avatar);

		$event = $this->service->get(3);

		$this->assertInternalType('int', $event->id);
		$this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_time);
		$this->assertNull($event->event_end);
		$this->assertInstanceOf('\DateTime', $event->event_date);
		$this->assertInternalType('int', $event->capacity);
		$this->assertNotNull($event->avatar);
	}

    /**
     *
     */
    protected function setUp()
	{
		$serviceManager = Bootstrap::getServiceManager();
		$this->config = $serviceManager->get('Config');
        $conn=$this->getConnection();
        $conn->getConnection()->query("set foreign_key_checks=0");
        parent::setUp();
        $conn->getConnection()->query("set foreign_key_checks=1");

		$service = new Event();
		$service->setDataSource(self::$pdo);

		$this->service = $service;
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
				[ 'id'=>1, 'name'=>'name1', 'name_short'=>'n1', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n1' ],
				[ 'id'=>2, 'name'=>'name2', 'name_short'=>'n2', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n2' ],
				[ 'id'=>3, 'name'=>'name3', 'name_short'=>'n3', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n3' ],
				[ 'id'=>4, 'name'=>'name4', 'name_short'=>'n4', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n4' ],
			],
			'Event' => [
				['id'=>1, 'subject'=>'01', 'body'=>'01', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-4 days')),'event_time'=>date('H:m'),'event_end'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null,'capacity'=>0],
				['id'=>2, 'subject'=>'01', 'body'=>'01', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-4 days')),'event_time'=>date('H:m'),'event_end'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null,'capacity'=>null],
				['id'=>3, 'subject'=>'01', 'body'=>'01', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-4 days')),'event_time'=>date('H:m'),'event_end'=>null,	   'avatar'=>'df','lat'=>null,'lng'=>null,'capacity'=>1],

			],
			'Group_has_Event' => [
				['event_id'=>2, 'group_id'=>1,'primary'=>0],
				['event_id'=>2, 'group_id'=>2,'primary'=>0],
				['event_id'=>2, 'group_id'=>3,'primary'=>0],

				['event_id'=>3, 'group_id'=>2,'primary'=>0],

			],
			'Event_has_Guest' => [
				['event_id'=>1,'name'=>'n1','email'=>'e@a.is','register_time'=>date('Y-m-d H:i:s')],
				['event_id'=>1,'name'=>'n2','email'=>'b@a.is','register_time'=>date('Y-m-d H:i:s')],

			],
			'Event_has_User' => [
				['event_id' => 1, 'user_id'=>1,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],


				['event_id' => 2, 'user_id'=>2,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],
			],
			'EventMedia' => [
				['id' => 1, 'name' => 'hundur1', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
				['id' => 2, 'name' => 'hundur2', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
				['id' => 3, 'name' => 'hundur3', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
				['id' => 4, 'name' => 'hundur4', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
				['id' => 5, 'name' => 'hundur5', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
				['id' => 6, 'name' => 'hundur6', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
			],

		]);
    }
}
