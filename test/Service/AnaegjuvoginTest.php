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

class AnaegjuvoginTest extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;

    private $conn = null;

    private $config;

    public function testGet()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(self::$pdo);

        $this->assertInstanceOf('\stdClass', $service->get(1));
        $this->assertFalse($service->get(1000));
    }

    /**
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Can't get Anaegjuvogin:[1]
     * @throws Exception
     */
    public function testGetException()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(new PDOMock());

        $this->assertInstanceOf('\stdClass', $service->get(1));
    }

    public function testGetYear()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(self::$pdo);

        $this->assertInstanceOf('\stdClass', $service->getYear(2001));
        $this->assertFalse($service->getYear(4000));
    }

    /**
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Can't get Anaegjuvogin by year:[2001]
     * @throws Exception
     */
    public function testGetYearException()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(new PDOMock());

        $this->assertInstanceOf('\stdClass', $service->getYear(2001));
    }

    public function testGetIndex()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(self::$pdo);

        $this->assertInstanceOf('\stdClass', $service->getIndex());
    }

    /**
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Can't get Anaegjuvogin index (where year IS NULL).
     * @throws Exception
     */
    public function testGetIndexException()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(new PDOMock());

        $this->assertInstanceOf('\stdClass', $service->getIndex());
    }

    public function testFetchAll()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(self::$pdo);

        $this->assertInternalType('array', $service->fetchAll());
    }

    /**
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Can't fetch all Anaegjuvogin.
     * @throws Exception
     */
    public function testFetchAllException()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(new PDOMock());

        $this->assertInstanceOf('\stdClass', $service->fetchAll());
    }

    public function testFetchYear()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(self::$pdo);

        $this->assertInternalType('array', $service->fetchYears());
    }

    /**
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Can't fetch all Anaegjuvogin.
     * @throws Exception
     */
    public function testFetchYearException()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(new PDOMock());

        $this->assertInstanceOf('\stdClass', $service->fetchYears());
    }

    public function testUpdate()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(self::$pdo);

        $rowCount = $service->update(1, [
            'name' => 'n1',
            'body' => 'b1',
            'year' => 2005
        ]);

        $this->assertEquals(1, $rowCount);

        $rowCount = $service->update(10, [
            'name' => 'n1',
            'body' => 'b1',
            'year' => 2025
        ]);

        $this->assertEquals(0, $rowCount);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Can't update anaegjuvogin. anaegjuvogin:[1]
     * @throws Exception
     */
    public function testUpdateException()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(new PDOMock());

        $service->update(1, []);
    }


    public function testCreate()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(self::$pdo);

        $entryId = $service->create([
            'name' => 'n1',
            'body' => 'b1',
            'year' => 2005
        ]);

        $this->assertInternalType('int', $entryId);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Can't create anaegjuvogin. anaegjuvogin
     * @throws Exception
     */
    public function testCreateException()
    {
        $service = new Anaegjuvogin();
        $service->setDataSource(new PDOMock());

        $service->create([]);
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
