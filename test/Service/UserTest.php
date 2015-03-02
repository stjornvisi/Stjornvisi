<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/22/14
 * Time: 8:38 PM
 */

namespace Stjornvisi\Service;

require_once __DIR__.'/../ArrayDataSet.php';
require_once __DIR__.'/../PDOMock.php';

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\PDOMock;
use Stjornvisi\Bootstrap;

class UserTest extends PHPUnit_Extensions_Database_TestCase {
    static private $pdo = null;
    private $conn = null;
	private $config;

    /**
	 * Try to get user when there is
	 * no connection to storage.
     * @expectedException Exception
     */
    public function testGetException(){
        $service = new User( new PDOMock() );
        $service->get(1);
    }

	/**
	 * Get user by ID and email,
	 * both valid IDs and emails as well
	 * as invalid values (which should return FALSE)
	 */
	public function testGet(){
		$service = new User( self::$pdo );

		$result = $service->get(1);
		$this->assertEquals('one@mail.com',$result->email);

		$result = $service->get('one@mail.com');
		$this->assertEquals('one@mail.com',$result->email);

		$result = $service->get(100);
		$this->assertFalse($result);

		$result = $service->get('one@mail123.com');
		$this->assertFalse($result);
	}

	/**
	 * Get all users
	 */
	public function testFetchAll(){
		$service = new User( self::$pdo );
		$result = $service->fetchAll();
		$this->assertCount(8,$result);
	}

	/**
	 * Get all users when there is no
	 * storage connection
	 * @expectedException Exception
	 */
	public function testFetchAllException(){
		$service = new User( new PDOMock() );
		$service->fetchAll();
	}

    public function testGetByGroup(){
        $userService = new User( self::$pdo );
        $this->assertEquals(7, count($userService->getByGroup(5,null)));
        $this->assertEquals(2, count($userService->getByGroup(5,2)));
        $this->assertEquals(3, count($userService->getByGroup(5,1)));
        $this->assertEquals(2, count($userService->getByGroup(5,0)));
    }

	/**
	 * @expectedException Exception
	 */
	public function testGetByGroupException(){
		$userService = new User( new PDOMock() );
		$this->assertEquals(7, count($userService->getByGroup(5,null)));
	}

    public function testGetTypeByGroupArray(){
        $userService = new User( self::$pdo );

        $d1 = $userService->getTypeByGroup(3,array(5,2));
        $this->assertEquals(1,$d1->type);
        $d1 = $userService->getTypeByGroup(3,array(2,5));
        $this->assertEquals(1,$d1->type);

        $d2 = $userService->getTypeByGroup(1,array(1,5,2));
        $this->assertEquals(2,$d2->type);
        $d2 = $userService->getTypeByGroup(1,array(2,1,5));
        $this->assertEquals(2,$d2->type);

        $d3 = $userService->getTypeByGroup(8,array(1,5,2));
        $this->assertNull($d3->type);
        $d3 = $userService->getTypeByGroup(8,array(5,2,1));
        $this->assertNull($d3->type);

        $d4 = $userService->getTypeByGroup(5,array(1,2,5));
        $this->assertEquals(1,$d4->type);
        $d4 = $userService->getTypeByGroup(5,array(2,1,5));
        $this->assertEquals(1,$d4->type);

        $d5 = $userService->getTypeByGroup(null,array(2,1,5));
        $this->assertNull($d5->type);
    }

    public function testGetTypeByGroupNullUser(){
        $userService = new User( self::$pdo );
        $result = $userService->getTypeByGroup(null,1);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertObjectHasAttribute('is_admin',$result);
        $this->assertObjectHasAttribute('type',$result);
        $this->assertEquals(false,$result->is_admin);
        $this->assertEquals(null,$result->type);
    }

    public function testGetTypeByGroupNullGroup(){
        $userService = new User( self::$pdo );
        $result = $userService->getTypeByGroup(1,null);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertObjectHasAttribute('is_admin',$result);
        $this->assertObjectHasAttribute('type',$result);
        $this->assertEquals(true,(bool)$result->is_admin);
        $this->assertEquals(null,$result->type);


        $userService = new User( self::$pdo );
        $result = $userService->getTypeByGroup(2,null);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertObjectHasAttribute('is_admin',$result);
        $this->assertObjectHasAttribute('type',$result);
        $this->assertEquals(false,(bool)$result->is_admin);
        $this->assertEquals(null,$result->type);
    }

    public function testGetTypeByGroupUserInGroup(){
        $userService = new User( self::$pdo );
        $result = $userService->getTypeByGroup(1,1);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertObjectHasAttribute('is_admin',$result);
        $this->assertObjectHasAttribute('type',$result);
        $this->assertEquals(true,(bool)$result->is_admin);
        $this->assertEquals(2,$result->type);

        $userService = new User( self::$pdo );
        $result = $userService->getTypeByGroup(1,2);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertObjectHasAttribute('is_admin',$result);
        $this->assertObjectHasAttribute('type',$result);
        $this->assertEquals(true,(bool)$result->is_admin);
        $this->assertEquals(1,$result->type);


        $userService = new User( self::$pdo );
        $result = $userService->getTypeByGroup(2,2);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertObjectHasAttribute('is_admin',$result);
        $this->assertObjectHasAttribute('type',$result);
        $this->assertEquals(false,(bool)$result->is_admin);
        $this->assertEquals(0,$result->type);
    }

    public function testGetTypeByGroupUserNotInGroup(){
        $userService = new User( self::$pdo );
        $result = $userService->getTypeByGroup(3,4);
        $this->assertInstanceOf('stdClass',$result);
        $this->assertObjectHasAttribute('is_admin',$result);
        $this->assertObjectHasAttribute('type',$result);
        $this->assertEquals(false,(bool)$result->is_admin);
        $this->assertEquals(null,$result->type);

    }

	public function testGetType(){
		$service = new User( self::$pdo );
		$user1 = $service->getType(1);
		$user2 = $service->getType(2);
		$user3 = $service->getType(100);
		$user4 = $service->getType(null);

		$this->assertTrue($user1->is_admin,'Exists, is admin');
		$this->assertFalse($user2->is_admin,'Exists, is not admin');
		$this->assertFalse($user3->is_admin,'Does not exists');
		$this->assertFalse($user4->is_admin,'ID is null');
	}


	/**
	 * Set password for found user as well
	 * as for one that does not exists.
	 */
	public function testSetPassword(){
		$service = new User( self::$pdo );

		$result = $service->setPassword(1,'hundur');
		$this->assertEquals(1,$result);

		$result = $service->setPassword(100,'hundur');
		$this->assertEquals(0,$result);
	}

	/**
	 * Set password for user then there is no
	 * connection to storage.
	 * @expectedException Exception
	 */
	public function testSetPasswordException(){
		$service = new User( new PDOMock() );
		$service->setPassword(1,'hundur');
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
        return new ArrayDataSet(include __DIR__.'/../data/user.01.php');
    }
} 
