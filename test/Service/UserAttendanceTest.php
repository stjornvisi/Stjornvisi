<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/22/14
 * Time: 3:46 PM
 */

namespace Stjornvisi\Service;

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\Bootstrap;


class UserAttendanceTest extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;

    private $conn = null;

	private $config;

    public function testTrue()
	{
        $service = new User();
		$service->setDataSource(self::$pdo);
        $service->attendance(1);
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
                [ 'id'=>1, 'name'=>'name1', 'name_short'=>'n1', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n1' ],
                [ 'id'=>2, 'name'=>'name2', 'name_short'=>'n2', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n2' ],
                [ 'id'=>3, 'name'=>'name3', 'name_short'=>'n3', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n3' ],
                [ 'id'=>4, 'name'=>'name4', 'name_short'=>'n4', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n4' ],
                [ 'id'=>5, 'name'=>'name4', 'name_short'=>'n4', 'description'=>'', 'objective'=>'', 'what_is'=>'', 'how_operates'=>'', 'for_whom'=>'', 'url'=>'n5' ],
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
            'Group_has_User' => [
                [ 'group_id'=>1, 'user_id'=>1, 'type'=>2 ],
                [ 'group_id'=>2, 'user_id'=>1, 'type'=>1 ],
                [ 'group_id'=>2, 'user_id'=>2, 'type'=>0 ],
                [ 'group_id'=>2, 'user_id'=>3, 'type'=>0 ],

                [ 'group_id'=>5, 'user_id'=>1, 'type'=>2 ],
                [ 'group_id'=>5, 'user_id'=>2, 'type'=>2 ],
                [ 'group_id'=>5, 'user_id'=>3, 'type'=>1 ],
                [ 'group_id'=>5, 'user_id'=>4, 'type'=>1 ],
                [ 'group_id'=>5, 'user_id'=>5, 'type'=>1 ],
                [ 'group_id'=>5, 'user_id'=>6, 'type'=>0 ],
                [ 'group_id'=>5, 'user_id'=>7, 'type'=>0 ],
            ],
        ]);
    }
}
