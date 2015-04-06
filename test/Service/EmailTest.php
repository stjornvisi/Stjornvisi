<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 5/04/15
 * Time: 11:02 AM
 */

namespace Stjornvisi\Service;

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\PDOMock;
use Stjornvisi\Bootstrap;

class EmailTest extends PHPUnit_Extensions_Database_TestCase
{
	static private $pdo = null;

	private $conn = null;

	private $config;

	public function testCreate()
	{
		$service = new Email();
		$service->setDataSource(self::$pdo);

		$rowCount = $service->create([
			'subject' => 's1',
			'hash' => 'h1',
			'user_hash' => 'u1',
			'type' => 't1'
		]);
		$this->assertInternalType('int', $rowCount);
	}

	/**
	 *
	 * @expectedException \Exception
	 * @expectedExceptionMessage Can't create Email record
	 * @throws Exception
	 */
	public function testGetException()
	{
		$service = new Email();
		$service->setDataSource(new PDOMock());

		$rowCount = $service->create([]);
		$this->assertInternalType('int', $rowCount);
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
			'Anaegjuvogin' => [
				['id'=>1,'name'=>'','body'=>'','year'=>2001],
				['id'=>2,'name'=>'','body'=>'','year'=>2002],
				['id'=>3,'name'=>'','body'=>'','year'=>2003],
				['id'=>4,'name'=>'','body'=>'','year'=>2004],
				['id'=>5,'name'=>'','body'=>'','year'=>2005],
				['id'=>6,'name'=>'','body'=>'','year'=>2006],
				['id'=>7,'name'=>'','body'=>'','year'=>2007],
				['id'=>8,'name'=>'','body'=>'','year'=>2008],
				['id'=>9,'name'=>'','body'=>'','year'=>null],
			],
		]);
	}
}