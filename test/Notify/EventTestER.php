<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 30/09/14
 * Time: 19:26
 */

namespace Stjornvisi\Notify;

require_once __DIR__.'/../ArrayDataSet.php';
require_once __DIR__.'/../PDOMock.php';

use PHPUnit_Extensions_Database_TestCase;
use \PDO;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

use Stjornvisi\Notify\Event;
use Stjornvisi\Service\User as UserService;
use Stjornvisi\Service\Event as EventService;

use Stjornvisi\ArrayDataSet;

class EventTest extends PHPUnit_Extensions_Database_TestCase {

	static private $pdo = null;
	private $conn = null;

	public function testAllTest(){

		$data = (object)array(
			'action' => \Stjornvisi\Notify\Event::MESSAGING,
			'data' => (object)array(
					'event_id' => 1,
					'recipients' => 'allir', //( $this->params()->fromRoute('type', 'allir') ),
					'test' => true,//(bool)$this->params()->fromPost('test',false),
					'subject' => '',//$form->get('subject')->getValue(),
					'body' => '',//$form->get('body')->getValue(),
					'user_id' => 1
				),
		);

		$logger = new Logger;
		$writer = new Stream('php://output');
		$logger->addWriter($writer);

		$obj = new Event(
			new UserService( self::$pdo ),
			new EventService( self::$pdo )
		);
		$obj->setData($data);
		$obj->setLogger($logger);
		$obj->send();
	}

	public function testNotAllTest(){

		$data = (object)array(
			'action' => \Stjornvisi\Notify\Event::MESSAGING,
			'data' => (object)array(
					'event_id' => 1,
					'recipients' => 'notallir', //( $this->params()->fromRoute('type', 'allir') ),
					'test' => true,//(bool)$this->params()->fromPost('test',false),
					'subject' => '',//$form->get('subject')->getValue(),
					'body' => '',//$form->get('body')->getValue(),
					'user_id' => 1
				),
		);

		$logger = new Logger;
		$writer = new Stream('php://output');
		$logger->addWriter($writer);

		$obj = new Event(
			new UserService( self::$pdo ),
			new EventService( self::$pdo )
		);
		$obj->setData($data);
		$obj->setLogger($logger);
		$obj->send();
	}

	public function testAllNotTest(){

		$data = (object)array(
			'action' => \Stjornvisi\Notify\Event::MESSAGING,
			'data' => (object)array(
					'event_id' => 1,
					'recipients' => 'allir', //( $this->params()->fromRoute('type', 'allir') ),
					'test' => false,//(bool)$this->params()->fromPost('test',false),
					'subject' => '',//$form->get('subject')->getValue(),
					'body' => '',//$form->get('body')->getValue(),
					'user_id' => 1
				),
		);

		$logger = new Logger;
		$writer = new Stream('php://output');
		$logger->addWriter($writer);

		$obj = new Event(
			new UserService( self::$pdo ),
			new EventService( self::$pdo )
		);
		$obj->setData($data);
		$obj->setLogger($logger);
		$obj->send();
	}

	public function testNotAllNotTest(){

		$data = (object)array(
			'action' => \Stjornvisi\Notify\Event::MESSAGING,
			'data' => (object)array(
					'event_id' => 1,
					'recipients' => 'notallir', //( $this->params()->fromRoute('type', 'allir') ),
					'test' => false,//(bool)$this->params()->fromPost('test',false),
					'subject' => '',//$form->get('subject')->getValue(),
					'body' => '',//$form->get('body')->getValue(),
					'user_id' => 1
				),
		);

		$logger = new Logger;
		$writer = new Stream('php://output');
		$logger->addWriter($writer);

		$obj = new Event(
			new UserService( self::$pdo ),
			new EventService( self::$pdo )
		);
		$obj->setData($data);
		$obj->setLogger($logger);
		$obj->send();
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
}
 