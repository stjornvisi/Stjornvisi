<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/4/14
 * Time: 11:17 AM
 */

namespace Stjornvisi\Service;


require_once __DIR__.'/../ArrayDataSet.php';
require_once __DIR__.'/../PDOMock.php';

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\PDOMock;

/**
 * Class ArticleTest
 *
 * @package Stjornvisi\Service
 * @coversDefaultClass \Stjornvisi\Service\Article
 */
class CompanyTest extends PHPUnit_Extensions_Database_TestCase{
    static private $pdo = null;
    private $conn = null;

	/**
	 * Get one item, should return FALSE if item not found
	 */
	public function testGet(){
		$service = new Company( self::$pdo );
		$result = $service->get(1);
		$this->assertInstanceOf('\stdClass',$result);

		$result = $service->get(100);
		$this->assertFalse($result);
	}

	/**
	 * Should throe exception if service can't
	 * connect to storage.
	 * @expectedException Exception
	 */
	public function testGetException(){
		$service = new Company( new PDOMock() );
		$service->get(1);
	}

	/**
	 * Get all companies and counting results,
	 * trying to filter out companies my business_type.
	 */
	public function testFetchAll(){
		$service = new Company( self::$pdo );
		$result = $service->fetchAll();
		$this->assertCount(4,$result);

		$result = $service->fetchAll(array('hf'));
		$this->assertCount(2,$result);

		$result = $service->fetchAll(array('sf','ohf'));
		$this->assertCount(2,$result);

		$result = $service->fetchAll(array('sf','ohf','hf'));
		$this->assertCount(0,$result);
	}

	/**
	 * @expectedException Exception
	 */
	public function testFetchAllException(){
		$service = new Company( new PDOMock() );
		$service->fetchAll();
	}

	/**
	 * Set new role for employee.
	 */
	public function testSetEmployeeRole(){
		$service = new Company( self::$pdo );
		$result = $service->setEmployeeRole(1,1,1);
		$this->assertEquals(1,$result);

		$result = $service->setEmployeeRole(1,1,1);
		$this->assertEquals(0,$result);

		$result = $service->setEmployeeRole(1,3,1);
		$this->assertEquals(0,$result);
	}

	/**
	 * @expectedException Exception
	 */
	public function testSetEmployeeRoleException(){
		$service = new Company( new PDOMock() );
		$service->setEmployeeRole(1,1,1);
	}

	/**
	 * Try to promote user to a role
	 * that is not allowed.
	 * @expectedException Exception
	 */
	public function testSetEmployeeRoleExceptionInvalidRoleType(){
		$service = new Company( new PDOMock() );
		$service->setEmployeeRole(1,1,10);
	}

	/**
	 * Get all companies of user.
	 * this is usually only one.
	 */
	public function testGetByUser(){
		$service = new Company( self::$pdo );
		$result = $service->getByUser(1);
		$this->assertCount(1,$result);

		$result = $service->getByUser(2);
		$this->assertCount(2,$result);

		$result = $service->getByUser(10);
		$this->assertCount(0,$result);
	}

	/**
	 * Get all companies of user
	 * without storage connection
	 * @expectedException Exception
	 */
	public function testGetByUserException(){
		$service = new Company( new PDOMock() );
		$service->getByUser(1);
	}

	/**
	 * Test update company.
	 * One can update a company that does not exists.
	 */
	public function testUpdate(){
		$service = new Company( self::$pdo );
		$result = $service->update(1,array(
			'submit' => 'submit',
			'name' => 'n33',
			'ssn' => '1029384756',
			'address' => 'a1',
			'zip' => '3124',
			'website' => null,
			'number_of_employees' => '+200',
			'safe_name' => 'sn1',
			'created' => date('Y-m-d H:i:s')
		));
		$this->assertEquals(1,$result);

		$result = $service->update(10,array(
			'submit' => 'submit',
			'name' => 'n33',
			'ssn' => '1029384756',
			'address' => 'a1',
			'zip' => '3124',
			'website' => null,
			'number_of_employees' => '+200',
			'safe_name' => 'sn1',
			'created' => date('Y-m-d H:i:s')
		));
		$this->assertEquals(0,$result);
	}

	/**
	 * Test update company.
	 * With invalid data
	 * @expectedException Exception
	 */
	public function testUpdateInvalid(){
		$service = new Company( self::$pdo );
		$result = $service->update(1,array(
			'submit' => 'submit',
			'gaman' => 'n33',
			'ssn' => '1029384756',
			'address' => 'a1',
			'zip' => '3124',
			'website' => null,
			'number_of_employees' => '+200',
			'safe_name' => 'sn1',
			'created' => date('Y-m-d H:i:s')
		));
		$this->assertEquals(1,$result);
	}

	/**
	 * Update without storage connection.
	 * @expectedException Exception
	 */
	public function testUpdateException(){
		$service = new Company( new PDOMock() );
		$service->update(1,array(
			'submit' => 'submit',
			'name' => 'n33',
			'ssn' => '1029384756',
			'address' => 'a1',
			'zip' => '3124',
			'website' => null,
			'number_of_employees' => '+200',
			'safe_name' => 'sn1',
			'created' => date('Y-m-d H:i:s')
		));
	}

	/**
	 * Test create company.
	 */
	public function testCreate(){
		$service = new Company( self::$pdo );
		$result = $service->create(array(
			'submit' => 'submit',
			'name' => 'n33',
			'ssn' => '1029384756',
			'address' => 'a1',
			'zip' => '3124',
			'website' => null,
			'number_of_employees' => '+200',
			'safe_name' => 'sn1',
			'created' => date('Y-m-d H:i:s')
		));
		$this->assertGreaterThan(4,$result);
	}

	/**
	 * Test update company.
	 * With invalid data
	 * @expectedException Exception
	 */
	public function testCreateInvalid(){
		$service = new Company( self::$pdo );
		$result = $service->create(array(
			'submit' => 'submit',
			'gaman' => 'n33',
			'ssn' => '1029384756',
			'address' => 'a1',
			'zip' => '3124',
			'website' => null,
			'number_of_employees' => '+200',
			'safe_name' => 'sn1',
			'created' => date('Y-m-d H:i:s')
		));
	}

	/**
	 * Update without storage connection.
	 * @expectedException Exception
	 */
	public function testCreateException(){
		$service = new Company( new PDOMock() );
		$service->create(array(
			'submit' => 'submit',
			'name' => 'n33',
			'ssn' => '1029384756',
			'address' => 'a1',
			'zip' => '3124',
			'website' => null,
			'number_of_employees' => '+200',
			'safe_name' => 'sn1',
			'created' => date('Y-m-d H:i:s')
		));
	}

	/**
	 * Add user to company
	 */
	public function testAddUser(){
		$service = new Company( self::$pdo );
		$count = $service->addUser(2,1,1);
		$this->assertEquals(1,$count);
	}

	/**
	 * Add user to company which is already
	 * connected
	 * @expectedException Exception
	 */
	public function testAddUserAlreadyConnected(){
		$service = new Company( self::$pdo );
		$count = $service->addUser(1,1,1);
		$this->assertEquals(1,$count);
	}

	/**
	 * Add user to company which is already
	 * connected
	 * @expectedException Exception
	 */
	public function testAddUserCompanyDoesNotExist(){
		$service = new Company( self::$pdo );
		$service->addUser(10,10,1);
	}

	/**
	 * Delete company from file.
	 */
	public function testDelete(){
		$service = new Company( self::$pdo );
		$count = $service->delete(1);
		$this->assertEquals(1,$count);

		$count = $service->delete(10);
		$this->assertEquals(0,$count);
	}

	/**
	 * Delete company with no storage connection.
	 * @expectedException Exception
	 */
	public function testDeleteException(){
		$service = new Company( new PDOMock() );
		$service->delete(1);
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
} 
