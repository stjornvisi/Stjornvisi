<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 22/03/14
 * Time: 19:50
 */

namespace Stjornvisi\Service;

require_once __DIR__.'/../ArrayDataSet.php';
require_once __DIR__.'/../PDOMock.php';

use \PDO;
use Stjornvisi\ArrayDataSet;
use PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\PDOMock;

class UserAccessTest extends PHPUnit_Extensions_Database_TestCase {
	static private $pdo = null;
	private $conn = null;

	/**
	 * Get type of user, i.e. if he
	 * is admin or not.
	 */
	public function testGetType(){
		$service = new User( self::$pdo );

		$result = $service->getType(1);
		$this->assertInstanceOf('\stdClass',$result,'User exists|Result type is stdClass');
		$this->assertEquals(1,$result->is_admin,'User exists|User is admin');
		$this->assertEquals(0,$result->type,'User exists|Type property is ambiguous, but SHOULD be 0');

		$result = $service->getType(2);
		$this->assertInstanceOf('\stdClass',$result,'User exists|Result type is stdClass');
		$this->assertEquals(0,$result->is_admin,'User exists|User is not admin');
		$this->assertEquals(0,$result->type,'User exists|Type property is ambiguous, but SHOULD be 0');

		$result = $service->getType(20);
		$this->assertInstanceOf('\stdClass',$result,'User not exists|Result type is stdClass');
		$this->assertEquals(0,$result->is_admin,'User not exists|User is not admin');
		$this->assertEquals(0,$result->type,'User not exists|Type property is ambiguous, but SHOULD be 0');

		$result = $service->getType(null);
		$this->assertInstanceOf('\stdClass',$result,'User null|Result type is stdClass');
		$this->assertEquals(0,$result->is_admin,'User null|User is not admin');
		$this->assertEquals(0,$result->type,'User null|Type property is ambiguous, but SHOULD be 0');
	}

	/**
	 * Try to get type of user with no
	 * storage connection
	 * @expectedException Exception
	 */
	public function testGetTypeException(){
		$service = new User( new PDOMock() );
		$service->getType(1);
	}

	/**
	 * Test if 'not' logged in user has access
	 * in relation to groups, which he never has.
	 */
	public function testGetTypeByGroupAnonymousUser(){
		$service = new User( self::$pdo );
		$result = $service->getTypeByGroup(null,array());
		$this->assertFalse($result->is_admin);
		$this->assertNull($result->type);

		$result = $service->getTypeByGroup(null,array(1,2,3));
		$this->assertFalse($result->is_admin);
		$this->assertNull($result->type);
	}

	/**
	 * Test if 'not' logged in user has access
	 * when there is no connection. Since user
	 * with ID = null, will never have any access, this
	 * method will not access storage and will not
	 * throw an exception
	 */
	public function testGetTypeByGroupAnonymousUserException(){
		$service = new User( new PDOMock() );
		$result = $service->getTypeByGroup(null,array());
		$this->assertInstanceOf('\stdClass',$result);
	}

	/**
	 * Get type of user in relation to groups,
	 * where the group array is empty. Here we are
	 * basically checking if the user is admin 'cause
	 * user can never have access to group that checking
	 * against :)
	 */
	public function testGetTypeByGroupWithEmptyGroupArray(){
		$service = new User( self::$pdo );

		$result = $service->getTypeByGroup(1,array());
		$this->assertEquals(1,$result->is_admin,'User is admin');
		$this->assertNull($result->type,'User is admin');

		$result = $service->getTypeByGroup(2,array());
		$this->assertEquals(0,$result->is_admin,'User is not admin');
		$this->assertNull($result->type,'User is not admin');

		$result = $service->getTypeByGroup(200,array());
		$this->assertEquals(0,$result->is_admin,'User does not exists');
		$this->assertNull($result->type,'User does not exists');
	}

	/**
	 * Get type of user in relation to groups.
	 */
	public function testGetTypeByGroup(){
		$service = new User( self::$pdo );
		$result = $service->getTypeByGroup(1,array(1));
		$this->assertEquals(1,$result->is_admin);
		$this->assertEquals(0,$result->type,'User has no access to group');

		$result = $service->getTypeByGroup(2,array(1));
		$this->assertEquals(0,$result->is_admin);
		$this->assertEquals(1,$result->type,'User is manager of group');

		$result = $service->getTypeByGroup(2,array(2));
		$this->assertEquals(0,$result->is_admin);
		$this->assertEquals(2,$result->type,'User is chairman of group');

		$result = $service->getTypeByGroup(2,array(1,2));
		$this->assertEquals(0,$result->is_admin);
		$this->assertEquals(2,$result->type,'User is manager of 1, but chairman of 2');

		$result = $service->getTypeByGroup(2,array(2,1,2));
		$this->assertEquals(0,$result->is_admin);
		$this->assertEquals(2,$result->type,'User is manager of 1, but chairman of 2 (reverse)');

		$result = $service->getTypeByGroup(2,array(3,4));
		$this->assertEquals(0,$result->is_admin);
		$this->assertNull($result->type,'User has no access to 3 and 4');

		$result = $service->getTypeByGroup(2,array(4,3,4));
		$this->assertEquals(0,$result->is_admin);
		$this->assertNull($result->type,'User has no access to 3 and 4 (reverse)');

		$result = $service->getTypeByGroup(2,array(4,3,null));
		$this->assertEquals(0,$result->is_admin);
		$this->assertNull($result->type,'User has no access to 3 and 4 (reverse with null)');

		$result = $service->getTypeByGroup(2,4);
		$this->assertEquals(0,$result->is_admin);
		$this->assertNull($result->type,'User has no access to 4');

		$result = $service->getTypeByGroup(2,1);
		$this->assertEquals(0,$result->is_admin);
		$this->assertEquals(1,$result->type,'User has access to 1');

		$result = $service->getTypeByGroup(2,2);
		$this->assertEquals(0,$result->is_admin);
		$this->assertEquals(2,$result->type,'User has access to 2');
	}

	/**
	 * @expectedException Exception
	 */
	public function testGetTypeByGroupException(){
		$service = new User( new PDOMock() );
		$service->getTypeByGroup(1,array(1,2));
	}

	/**
	 * Test if anonymous user has access to company
	 */
	public function testGetTypeByCompanyAnonymousUser(){
		$service = new User( self::$pdo );
		$result = $service->getTypeByCompany(null,1);
		$this->assertEquals(0,$result->is_admin);
		$this->assertNull($result->type);

		$result = $service->getTypeByCompany(null,null);
		$this->assertEquals(0,$result->is_admin);
		$this->assertNull($result->type);
	}

	/**
	 * Test user access to company.
	 */
	public function testGetTypeByCompany(){
		$service = new User( self::$pdo );
		$result = $service->getTypeByCompany(1,1);
		$this->assertEquals(0,$result->type,'User in company, not key_user');

		$result = $service->getTypeByCompany(2,1);
		$this->assertEquals(1,$result->type,'User in company, key_user');

		$result = $service->getTypeByCompany(1,2);
		$this->assertNull($result->type,'User not in company');

		$result = $service->getTypeByCompany(100,2);
		$this->assertNull($result->type,'User does not exists');

		$result = $service->getTypeByCompany(1,200);
		$this->assertNull($result->type,'Company does not exists');

		$result = $service->getTypeByCompany(100,200);
		$this->assertNull($result->type,'Company and user does not exists');
	}

	/**
	 * Test connection from user to company
	 * when there is no connection to storage.
	 * @expectedException Exception
	 */
	public function testGetTypeByCompanyException(){
		$service = new User( new PDOMock() );
		$service->getTypeByCompany(1,1);
	}

	/**
	 * Test for user has access to user.
	 */
	public function testGetTypeByUser(){
		$service = new User( self::$pdo );
		$result = $service->getTypeByUser(null,null);
		$this->assertEquals(0,$result->is_admin);
		$this->assertEquals(0,$result->type);

		$result = $service->getTypeByUser(2,1);
		$this->assertEquals(1,$result->is_admin);
		$this->assertEquals(0,$result->type);

		$result = $service->getTypeByUser(2,2);
		$this->assertEquals(0,$result->is_admin);
		$this->assertEquals(1,$result->type);
	}

	/**
	 * Test for user has access to user
	 * when there is no connection to storage.
	 * @expectedException Exception
	 */
	public function testGetTypeByUserException(){
		$service = new User( new PDOMock() );
		$service->getTypeByUser(2,1);
	}

	/**
	 * Change type of user.
	 */
	public function testSetType(){
		$service = new User( self::$pdo );
		$result = $service->setType(2,1);
		$this->assertEquals(1,$result);
	}

	/**
	 * Try to change type of user
	 * when there is no connection to
	 * storage.
	 * @expectedException Exception
	 */
	public function testSetTypeException(){
		$service = new User( new PDOMock() );
		$service->setType(2,1);
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
		return new ArrayDataSet([
			'User' => [
				['id'=>1, 'name'=>'n1', 'passwd'=>'p1', 'email'=>'one@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>1],
				['id'=>2, 'name'=>'n2', 'passwd'=>'p2', 'email'=>'two@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
				['id'=>3, 'name'=>'n3', 'passwd'=>'p3', 'email'=>'thr@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
				['id'=>4, 'name'=>'n4', 'passwd'=>'p4', 'email'=>'fou@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
				['id'=>5, 'name'=>'n5', 'passwd'=>'p5', 'email'=>'fiv@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
				['id'=>6, 'name'=>'n6', 'passwd'=>'p6', 'email'=>'six@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
			],
			'Group' => [
				['id'=>1,'name'=>'n1', 'name_short'=>'ns1','url'=>'ns1'],
				['id'=>2,'name'=>'n2', 'name_short'=>'ns2','url'=>'ns2'],
				['id'=>3,'name'=>'n3', 'name_short'=>'ns3','url'=>'ns3'],
				['id'=>4,'name'=>'n4', 'name_short'=>'ns4','url'=>'ns4'],
				['id'=>5,'name'=>'n5', 'name_short'=>'ns5','url'=>'ns5'],
				['id'=>6,'name'=>'n6', 'name_short'=>'ns6','url'=>'ns6'],
			],
			'Group_has_User' => [
				['group_id'=>1,'user_id'=>1,'type'=>0],
				['group_id'=>1,'user_id'=>2,'type'=>1],
				['group_id'=>2,'user_id'=>2,'type'=>2],
			],
			'Company' => [
				['id'=>1,'name'=>'n1','ssn'=>'1234567891','address'=>'a1','zip'=>'1','website'=>null,'number_of_employees'=>'','business_type'=>'hf','safe_name'=>'s1','created'=>date('Y-m-d H:i:s')],
				['id'=>2,'name'=>'n2','ssn'=>'1234567892','address'=>'a2','zip'=>'1','website'=>null,'number_of_employees'=>'','business_type'=>'hf','safe_name'=>'s2','created'=>date('Y-m-d H:i:s')],
				['id'=>3,'name'=>'n3','ssn'=>'1234567893','address'=>'a3','zip'=>'1','website'=>null,'number_of_employees'=>'','business_type'=>'hf','safe_name'=>'s3','created'=>date('Y-m-d H:i:s')],
			],
			'Company_has_User' => [
				['user_id'=>1,'company_id'=>1,'key_user'=>0],
				['user_id'=>2,'company_id'=>1,'key_user'=>1],
			],
		]);
	}
} 
