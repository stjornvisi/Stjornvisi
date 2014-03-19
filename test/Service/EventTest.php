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


class EventTest extends PHPUnit_Extensions_Database_TestCase{
    static private $pdo = null;
    private $conn = null;

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
                    'mysql:dbname=stjornvisi_test;host=127.0.0.1',
                    'root',
                    '',
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
        return new ArrayDataSet(include __DIR__.'/../data/event.01.php');
    }
} 
