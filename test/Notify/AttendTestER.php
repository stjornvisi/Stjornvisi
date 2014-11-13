<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 29/09/14
 * Time: 11:53
 */

namespace Stjornvisi\Notify;

require_once __DIR__.'/../ArrayDataSet.php';
require_once __DIR__.'/../PDOMock.php';

use PHPUnit_Extensions_Database_TestCase;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

use \PDO;
use Stjornvisi\Service\User;
use Stjornvisi\Service\Event;
use Stjornvisi\ArrayDataSet;


class AttendTest extends PHPUnit_Extensions_Database_TestCase {

	static private $pdo = null;
	private $conn = null;

	public function testUserExistsAndWantsToRegister(){

		$userDAO = new User( self::$pdo );
		$eventDAO = new Event( self::$pdo );

		$data = (object)array(
			'action' => \Stjornvisi\Notify\Attend::ATTENDING,
			'data' => (object)array(
					'recipients' => 1,
					'event_id' => 1,
					'type' => 1
				),
		);

		$logger = new Logger;
		$writer = new Stream('php://output');
		$logger->addWriter($writer);

		$object = new Attend($userDAO, $eventDAO);
		$object->setData($data);
		$object->setLogger($logger);
		$object->send();

	}

	public function testUserExistsAndWantsToUnRegister(){

		$userDAO = new User( self::$pdo );
		$eventDAO = new Event( self::$pdo );

		$data = (object)array(
			'action' => \Stjornvisi\Notify\Attend::ATTENDING,
			'data' => (object)array(
					'recipients' => 1,
					'event_id' => 1,
					'type' => 0
				),
		);

		$logger = new Logger;
		$writer = new Stream('php://output');
		$logger->addWriter($writer);

		$object = new Attend($userDAO, $eventDAO);
		$object->setData($data);
		$object->setLogger($logger);
		$object->send();

	}

	public function testUserDoesNotExistsButWantsToRegister(){

		$userDAO = new User( self::$pdo );
		$eventDAO = new Event( self::$pdo );

		$data = (object)array(
			'action' => \Stjornvisi\Notify\Attend::ATTENDING,
			'data' => (object)array(
					'recipients' => (object)array(
							'id' => null,
							'name' => 'My Name',
							'email' => 'my@name.com',
						),
					'event_id' => 1,
					'type' => 1
				),
		);

		$logger = new Logger;
		$writer = new Stream('php://output');
		$logger->addWriter($writer);

		$object = new Attend($userDAO, $eventDAO);
		$object->setData($data);
		$object->setLogger($logger);
		$object->send();

	}

	public function testTrue(){

	}




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
					$GLOBALS['DB_DSN'],
					$GLOBALS['DB_USER'],
					$GLOBALS['DB_PASSWD'],
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
				['id'=>1,'name'=>'einar','passwd'=>'1234','created_date'=>date('Y-m-d'),'modified_date'=>date('Y-m-d')],
			],
			'Event' => [
				['id'=>1,'subject'=>'s1']
			],
		]);
	}
}
 