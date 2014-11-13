<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/9/14
 * Time: 3:10 PM
 */

namespace Stjornvisi\Service;

require_once __DIR__.'/../ArrayDataSet.php';
require_once __DIR__.'/../PDOMock.php';

use Stjornvisi\PDOMock;
use Stjornvisi\Service\Event;
use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\Bootstrap;


class EventTest extends PHPUnit_Extensions_Database_TestCase{
    static private $pdo = null;
    private $conn = null;
	private $config;

	/**
	 * Try to get event.
	 * If no event found, return FALSE.
	 */
	public function testGet(){
		$service = new Event( self::$pdo );
		$result = $service->get(1);
		$this->assertInstanceOf('\stdClass',$result);

		$result = $service->get(100);
		$this->assertFalse($result);
	}

	public function testGetIfAttendersArePresent(){
		$service = new Event( self::$pdo );
		$result = $service->get(1);
		$this->assertInternalType('array',$result->attenders);
		$this->assertCount(0,$result->attenders,'Event #1 has attendance, but the event has passed');

		$result = $service->get(9);
		$this->assertInternalType('array',$result->attenders);
		$this->assertCount(3,$result->attenders,'Event #1 has attendance, but the event has passed');
	}

	/**
	 * Try to get event with out connection
	 * to storage
	 * @expectedException Exception
	 */
	public function testGetException(){
		$service = new Event( new PDOMock() );
		$service->get(1);
	}

	public function testGetNext(){

	}




	// - - - - - - - - - - - - - - - - - - - - - - - -
/*
    public function testRegisterUserToEventById(){
        $eventService = new Event( self::$pdo );
        $eventService->registerUser(1,1); //I can run it multiple times :)
        $eventService->registerUser(1,1);
        $eventService->registerUser(1,1);
        $eventService->registerUser(1,1);

        $queryTable = $this->getConnection()->createQueryTable(
            'Event_has_User', 'SELECT event_id, user_id FROM Event_has_User'
        );

        $expectedTable = $this->createFlatXmlDataSet(__DIR__."/../data/expected-Event_has_User.01.xml")
            ->getTable("Event_has_User");
        $this->assertTablesEqual($expectedTable, $queryTable);

    }

    public function testRegisterUserToEventByEmailHasAnAccount(){
        $eventService = new Event( self::$pdo );
        $eventService->registerUser(1,'e@mail.com');

        $queryTable = $this->getConnection()->createQueryTable(
            'Event_has_User', 'SELECT event_id, user_id FROM Event_has_User'
        );

        $expectedTable = $this->createFlatXmlDataSet(__DIR__."/../data/expected-Event_has_User.01.xml")
            ->getTable("Event_has_User");
        $this->assertTablesEqual($expectedTable, $queryTable);

    }

    public function testRegisterUserToEventByEmailDoesNotHaveAccount(){
        $eventService = new Event( self::$pdo );
        $eventService->registerUser(1,'abc@mail.com');

        $queryTable = $this->getConnection()->createQueryTable(
            'Event_has_Guest', 'SELECT event_id, email FROM Event_has_Guest'
        );

        $expectedTable = $this->createFlatXmlDataSet(__DIR__."/../data/expected-Event_has_Guest.01.xml")
            ->getTable("Event_has_Guest");
        $this->assertTablesEqual($expectedTable, $queryTable);

    }

    public function testUpdateOne(){
        $event = new Event( self::$pdo );

        $event->update(1,[
            'subject'=>'subject 1',
            'body'=>'',
            'location'=>'',
            'address'=>'',
            'event_date'=>date('Y-m-d'),
            'event_time'=>'00:00',
            'event_end'=>'00:00',
            'avatar'=>'',
            'lat' => null,
            'lng'=>null,
            'groups' => [1]

        ]);

        $queryTable = $this->getConnection()->createQueryTable(
            'Group_has_Event', 'SELECT * FROM Group_has_Event WHERE event_id = 1'
        );

        $expectedTable = $this->createFlatXmlDataSet(__DIR__."/../data/expected-Group_has_Event.01.xml")
            ->getTable("Group_has_Event");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testUpdateTwo(){
        $event = new Event( self::$pdo );

        $event->update(1,[
            'subject'=>'subject 1',
            'body'=>'',
            'location'=>'',
            'address'=>'',
            'event_date'=>date('Y-m-d'),
            'event_time'=>'00:00',
            'event_end'=>'00:00',
            'avatar'=>'',
            'lat' => null,
            'lng'=>null,
            'groups' => [1,2,3]

        ]);

        $queryTable = $this->getConnection()->createQueryTable(
            'Group_has_Event', 'SELECT * FROM Group_has_Event WHERE event_id = 1'
        );

        $expectedTable = $this->createFlatXmlDataSet(__DIR__."/../data/expected-Group_has_Event.02.xml")
            ->getTable("Group_has_Event");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }

    public function testUpdateThree(){
        $event = new Event( self::$pdo );

        $event->update(2,[
            'subject'=>'subject 2',
            'body'=>'',
            'location'=>'',
            'address'=>'',
            'event_date'=>date('Y-m-d'),
            'event_time'=>'00:00',
            'event_end'=>'00:00',
            'avatar'=>'',
            'lat' => null,
            'lng'=>null,
            'groups' => [1,3]

        ]);

        $queryTable = $this->getConnection()->createQueryTable(
            'Group_has_Event', 'SELECT * FROM Group_has_Event'
        );

        $expectedTable = $this->createFlatXmlDataSet(__DIR__."/../data/expected-Group_has_Event.03.xml")
            ->getTable("Group_has_Event");
        $this->assertTablesEqual($expectedTable, $queryTable);
    }
*/
    /**
     *
     */
    protected function setUp() {
		$serviceManager = Bootstrap::getServiceManager();
		$this->config = $serviceManager->get('Config');
        $conn=$this->getConnection();
        $conn->getConnection()->query("set foreign_key_checks=0");
        parent::setUp();
        $conn->getConnection()->query("set foreign_key_checks=1");
    }

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection(){

        if( $this->conn === null ){
            if (self::$pdo == null){
                self::$pdo = new PDO(
					$this->config['db']['dns'],
					$this->config['db']['user'],
					$this->config['db']['password'],
                    array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    ));
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo);
        }

        return $this->conn;
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        return new ArrayDataSet([
			'User' => [
				['id'=>1, 'name'=>'n1', 'passwd'=>md5(rand(0,9)), 'email'=>'e@mail.com', 'title'=>'t1', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
			],
			'Group' => [
				[ 'id'=>1, 'name'=>'name1', 'name_short'=>'n1', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n1' ],
				[ 'id'=>2, 'name'=>'name2', 'name_short'=>'n2', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n2' ],
				[ 'id'=>3, 'name'=>'name3', 'name_short'=>'n3', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n3' ],
				[ 'id'=>4, 'name'=>'name4', 'name_short'=>'n4', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n4' ],
			],
			'Event' => [
				['id'=>1, 'subject'=>'01', 'body'=>'01',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d',strtotime('-4 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
				['id'=>2, 'subject'=>'02', 'body'=>'02',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d',strtotime('-3 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
				['id'=>3, 'subject'=>'03', 'body'=>'03',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d',strtotime('-2 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
				['id'=>4, 'subject'=>'04', 'body'=>'04',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d',strtotime('-1 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
				['id'=>5, 'subject'=>'05', 'body'=>'05',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d'),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
				['id'=>6, 'subject'=>'06', 'body'=>'06',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d',strtotime('+1 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
				['id'=>7, 'subject'=>'07', 'body'=>'07',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d',strtotime('+2 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
				['id'=>8, 'subject'=>'08', 'body'=>'08',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d',strtotime('+3 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
				['id'=>9, 'subject'=>'09', 'body'=>'09',  'location'=>'01',   'address'=>'', 'event_date'=>date('Y-m-d',strtotime('+4 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
			],
			'Group_has_Event' => [
				['event_id'=>2, 'group_id'=>1,'primary'=>0],
				['event_id'=>2, 'group_id'=>2,'primary'=>0],
				['event_id'=>2, 'group_id'=>3,'primary'=>0],
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
			],

		]);
    }
} 
