<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/5/14
 * Time: 10:25 AM
 */

namespace Stjornvisi\Service;


require_once __DIR__.'/../ArrayDataSet.php';
require_once __DIR__.'/../PDOMock.php';

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\PDOMock;
use Stjornvisi\Bootstrap;

class BoardeTest extends PHPUnit_Extensions_Database_TestCase{
	static private $pdo = null;
	private $conn = null;
	private $config;
	/**
	 * Test get single period.
	 */
	public function testGetPeriod(){
		$service = new Board( self::$pdo );
		$result1 = $service->getBoard('2013-2014');
		$result2 = $service->getBoard('2012-2013');
		$result3 = $service->getBoard('2011-2012');

		$this->assertInternalType('array',$result1);
		$this->assertInternalType('array',$result2);
		$this->assertInternalType('array',$result3);

		$this->assertEquals(3, count($result1) );
		$this->assertEquals(2, count($result2) );
		$this->assertEquals(0, count($result3) );
	}

	/**
	 * Test get single period with no connection
	 * to the storage.
	 * @expectedException Exception
	 */
	public function testGetPeriodException(){
		$service = new Board( new PDOMock() );
		$service->getBoard('2012-2013');
	}

	/**
	 * Get all periods containing all board members.
	 */
	public function testGetBoardPeriods(){
		$service = new Board( self::$pdo );
		$result = $service->getPeriods();
		$this->assertEquals(2,count($result));
	}

	/**
	 * Test get all boards.
	 */
	public function testGetBoards(){
		$service = new Board( self::$pdo );
		$result = $service->getBoards();
		$this->assertCount(2,$result);
	}

	/**
	 * Test get all boards with no
	 * database connection.
	 * @expectedException Exception
	 */
	public function testGetBoardsException(){
		$service = new Board( new PDOMock() );
		$result = $service->getBoards();
		$this->assertCount(2,$result);
	}

	/**
	 * Get all periods containing all board members
	 * when there is no connection.
	 * @expectedException Exception
	 */
	public function testGetBoardPeriodsException(){
		$service = new Board( new PDOMock() );
		$service->getPeriods();
	}

	/**
	 * Get all members on file.
	 */
	public function testGetMembers(){
		$service = new Board( self::$pdo );
		$result = $service->getMembers();
		$this->assertEquals(3,count($result));
	}

	/**
	 * Get all members on file when
	 * there is no connection.
	 * @expectedException Exception
	 */
	public function testGetMembersException(){
		$service = new Board( new PDOMock() );
		$service->getMembers();
	}

	/**
	 * Get member on file that does not
	 * exists as well as one that does.
	 */
	public function testGetMember(){
		$service = new Board( self::$pdo );
		$result = $service->getMember(1);
		$this->assertInstanceOf('\stdClass',$result);

		$result = $service->getMember(100);
		$this->assertFalse($result);
	}

	/**
	 * Get member exception
	 * @expectedException Exception
	 */
	public function testGetMemberException(){
		$service = new Board( new PDOMock() );
		$service->getMember(1);
	}

	/**
	 * Create one bord member
	 */
	public function testCreateMember(){
		$service = new Board( self::$pdo );
		$id = $service->createMember(array(
			'name' => 'n1',
			'email' => 'e1',
			'company' => 'c1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
		$this->assertGreaterThan(3,$id);
	}

	/**
	 * Create one bord member
	 * @expectedException Exception
	 */
	public function testCreateMemberInvalidDate(){
		$service = new Board( self::$pdo );
		$id = $service->createMember(array(
			'hundur' => 'n1',
			'email' => 'e1',
			'company' => 'c1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
		$this->assertGreaterThan(3,$id);
	}

	/**
	 * Update one bord member
	 */
	public function testUpdateMember(){
		$service = new Board( self::$pdo );
		$count = $service->updateMember(1,array(
			'name' => 'n1'.rand(0,3),
			'email' => 'e1',
			'company' => 'c1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
		$this->assertEquals(1,$count);

		$count = $service->updateMember(100,array(
			'name' => 'n1'.rand(0,3),
			'email' => 'e1',
			'company' => 'c1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
		$this->assertEquals(0,$count);
	}

	/**
	 * Update one bord member
	 * @expectedException Exception
	 */
	public function testUpdateMemberInvalidData(){
		$service = new Board( self::$pdo );
		$count = $service->updateMember(1,array(
			'hundur' => 'n1'.rand(0,3),
			'email' => 'e1',
			'company' => 'c1',
			'avatar' => 'a1',
			'info' => 'i1',
			'submit' => 'submit'
		));
		$this->assertEquals(1,$count);
	}


	/**
	 * Get how many terms ara available
	 * from year 2000
	 * 2000-2001
	 * 2001-2002
	 * ...etc
	 */
	public function testGetTerms(){
		$service = new Board( self::$pdo );
		$result = $service->getTerms();
		$this->assertGreaterThan(14,$result);
	}

	/**
	 * Connect member on file with term.
	 */
	public function testConnectMember(){
		$service = new Board( self::$pdo );
		$count = $service->connectMember(array(
			'boardmember_id' => 1,
			'term' => '2001-2002',
			'is_chairman' => 1,
			'is_reserve' => 1,
			'is_manager' => 1
		));
		$this->assertEquals(1,$count);
	}

	/**
	 * Connect member on file with term
	 * but the member does not exist.
	 * @expectedException Exception
	 */
	public function testConnectMemberMemberNotFound(){
		$service = new Board( self::$pdo );
		$count = $service->connectMember(array(
			'boardmember_id' => 100,
			'term' => '2001-2002',
			'is_chairman' => 1,
			'is_reserve' => 1,
			'is_manager' => 1
		));
		$this->assertEquals(1,$count);
	}

	/**
	 * Disconnect member from term.
	 * Both connection that exists and
	 * one the does not
	 */
	public function testDisconnectMember(){
		$service = new Board( self::$pdo );
		$count = $service->disconnectMember(1);
		$this->assertEquals(1,$count);


		$count = $service->disconnectMember(100);
		$this->assertEquals(0,$count);
	}

	/**
	 * Disconnect member from term.
	 * @expectedException Exception
	 */
	public function testDisconnectMemberException(){
		$service = new Board( new PDOMock() );
		$count = $service->disconnectMember(1);
		$this->assertEquals(1,$count);
	}

	/**
	 * Get one connection from member to term.
	 * If connection is not found, return FALSE.
	 */
	public function testGetMemberConnection(){
		$service = new Board( self::$pdo );
		$result = $service->getMemberConnection(1);
		$this->assertInstanceOf('\stdClass',$result);

		$result = $service->getMemberConnection(100);
		$this->assertFalse($result);
	}


	/**
	 * Get one connection from member to term.
	 * without storage connection.
	 * @expectedException Exception
	 */
	public function testGetMemberConnectionException(){
		$service = new Board( new PDOMock() );
		$service->getMemberConnection(1);
	}

	/**
	 * Update member connection.
	 * One should be able to update connection
	 * that does not exist.
	 */
	public function testUpdateMemberConnection(){
		$service = new Board( self::$pdo );
		$count = $service->updateMemberConnection(1,array(
			'boardmember_id' => 1,
			'term' => '2013-2014',
			'is_chairman' => 1,
			'is_reserve' => 1,
			'is_manager' => 1
		));
		$this->assertEquals(1,$count);


		$count = $service->updateMemberConnection(100,array(
			'boardmember_id' => 1,
			'term' => '2013-2014',
			'is_chairman' => 1,
			'is_reserve' => 1,
			'is_manager' => 1
		));
		$this->assertEquals(0,$count);
	}

	/**
	 * Update member connection.
	 * One should be able to update connection
	 * that does not exist.
	 * @expectedException Exception
	 */
	public function testUpdateMemberConnectionMemberNotFound(){
		$service = new Board( self::$pdo );
		$count = $service->updateMemberConnection(1,array(
			'boardmember_id' => 100,
			'term' => '2013-2014',
			'is_chairman' => 1,
			'is_reserve' => 1,
			'is_manager' => 1
		));
		$this->assertEquals(1,$count);
	}

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
			'BoardMember' => [
				['id'=>1,'name'=>'n1','email'=>'e@a.is','company'=>'c1','avatar'=>'a1','info'=>'i1'],
				['id'=>2,'name'=>'n2','email'=>'e@b.is','company'=>'c2','avatar'=>'a2','info'=>'i2'],
				['id'=>3,'name'=>'n3','email'=>'e@c.is','company'=>'c3','avatar'=>'a3','info'=>'i3'],
			],
			'BoardMemberTerm' => [
				['id'=>1,'boardmember_id'=>1,'term'=>'2013-2014','is_chairman'=>1,'is_reserve'=>0,'is_manager'=>0],
				['id'=>2,'boardmember_id'=>2,'term'=>'2013-2014','is_chairman'=>0,'is_reserve'=>0,'is_manager'=>1],
				['id'=>3,'boardmember_id'=>3,'term'=>'2013-2014','is_chairman'=>0,'is_reserve'=>0,'is_manager'=>0],
				['id'=>4,'boardmember_id'=>1,'term'=>'2012-2013','is_chairman'=>1,'is_reserve'=>0,'is_manager'=>0],
				['id'=>5,'boardmember_id'=>2,'term'=>'2012-2013','is_chairman'=>0,'is_reserve'=>0,'is_manager'=>1],
			],
		]);
	}
} 
