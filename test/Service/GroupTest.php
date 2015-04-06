<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/4/14
 * Time: 11:17 AM
 */

namespace Stjornvisi\Service;

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\PDOMock;
use Stjornvisi\Bootstrap;

/**
 * Class ArticleTest
 *
 * @package Stjornvisi\Service
 * @coversDefaultClass \Stjornvisi\Service\Article
 */
class GroupTest extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;

    private $conn = null;

	private $config;

    /**
     * Test get.
     *
     * Should return stdClass if successful,
     * else false if entry not found.
     */
    public function testGet()
	{
        $service = new Group();
		$service->setDataSource(self::$pdo);

        $group1 = $service->get(1);
        $this->assertInstanceOf('\stdClass', $group1);

		$group2 = $service->get('n1');
		$this->assertInstanceOf('\stdClass', $group2);

		$group3 = $service->get(100);
        $this->assertFalse($group3);
    }

    /**
     * @expectedException Exception
     */
    public function testGetException()
	{
        $service = new Group();
		$service->setDataSource(new PDOMock());
        $service->get(1);
    }

	/**
	 * Get first year
	 */
	public function testGetFirstYear()
	{
		$service = new Group();
		$service->setDataSource(self::$pdo);

		$group1 = $service->getFirstYear(1);
		$this->assertInternalType('int', $group1);


		$group3 = $service->getFirstYear(100);
		$this->assertInternalType('int', $group3);
	}

	/**
	 * @expectedException Exception
	 */
	public function testGetFirstYearException()
	{
		$service = new Group();
		$service->setDataSource(new PDOMock());
		$service->getFirstYear(1);
	}

	/**
	 *
	 */
	public function testRegisterUser()
	{
		$service = new Group();
		$service->setDataSource(self::$pdo);

		$result = $service->registerUser(1, 1, true);
		$this->assertInternalType('int', $result);

		$result = $service->registerUser(1, 1, false);
		$this->assertInternalType('int', $result);
	}

	/**
	 * @expectedException Exception
	 */
	public function testRegisterUserExceptionTrue()
	{
		$service = new Group();
		$service->setDataSource(new PDOMock());
		$result = $service->registerUser(1,1,true);
	}

	/**
	 * @expectedException Exception
	 */
	public function testRegisterUserExceptionFalse()
	{
		$service = new Group();
		$service->setDataSource(new PDOMock());
		$result = $service->registerUser(1, 1, false);
	}


	/**
	 * Test get all groups by user.
	 */
	public function testGetByUser()
	{
		$service = new Group();
		$service->setDataSource(self::$pdo);

		$group1 = $service->getByUser(1);
		$this->assertInternalType('array', $group1);
	}

	/**
	 * @expectedException Exception
	 */
	public function testGetByUserException()
	{
		$service = new Group();
		$service->setDataSource(new PDOMock());
		$service->getByUser(1);
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
            'Group' => [
                ['id'=>1,'name'=>'nn1','name_short'=>'n1','description'=>'d1','objective'=>'o1','what_is'=>'w1','how_operates'=>'h1','for_whom'=>'f1','url'=>'n1'],
				['id'=>2,'name'=>'nn2','name_short'=>'n2','description'=>'d2','objective'=>'o2','what_is'=>'w2','how_operates'=>'h1','for_whom'=>'f1','url'=>'n2'],
				['id'=>3,'name'=>'nn3','name_short'=>'n3','description'=>'d3','objective'=>'o3','what_is'=>'w3','how_operates'=>'h1','for_whom'=>'f1','url'=>'n3'],
				['id'=>4,'name'=>'nn4','name_short'=>'n4','description'=>'d4','objective'=>'o4','what_is'=>'w4','how_operates'=>'h1','for_whom'=>'f1','url'=>'n4'],
            ],
			'User' => [
				['id'=>1, 'name'=>'', 'passwd'=>'', 'email'=>'one@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>1],
				['id'=>2, 'name'=>'', 'passwd'=>'', 'email'=>'two@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
				['id'=>3, 'name'=>'', 'passwd'=>'', 'email'=>'three@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],

				['id'=>4, 'name'=>'', 'passwd'=>'', 'email'=>'four@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
				['id'=>5, 'name'=>'', 'passwd'=>'', 'email'=>'five@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
				['id'=>6, 'name'=>'', 'passwd'=>'', 'email'=>'six@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
				['id'=>7, 'name'=>'', 'passwd'=>'', 'email'=>'seven@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
				['id'=>8, 'name'=>'', 'passwd'=>'', 'email'=>'eight@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
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
			'Group_has_User' => [],
			'Group_has_Event' => [
				['event_id'=>2, 'group_id'=>1,'primary'=>0],
				['event_id'=>2, 'group_id'=>2,'primary'=>0],
				['event_id'=>2, 'group_id'=>3,'primary'=>0],

				['event_id'=>3, 'group_id'=>2,'primary'=>0],
				['event_id'=>4, 'group_id'=>null,'primary'=>0],
			],
        ]);
    }
} 
