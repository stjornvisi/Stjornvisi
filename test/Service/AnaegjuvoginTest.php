<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 5/04/15
 * Time: 11:02 AM
 */

namespace Stjornvisi\Service;

use Stjornvisi\ArrayDataSet;

require_once 'AbstractServiceTest.php';
class AnaegjuvoginTest extends AbstractServiceTest
{
    public function testGet()
    {
        $service = $this->createService();

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
        $service = $this->createService(true);

        $this->assertInstanceOf('\stdClass', $service->get(1));
    }

    public function testGetYear()
    {
        $service = $this->createService();

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
        $service = $this->createService(true);

        $this->assertInstanceOf('\stdClass', $service->getYear(2001));
    }

    public function testGetIndex()
    {
        $service = $this->createService();

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
        $service = $this->createService(true);

        $this->assertInstanceOf('\stdClass', $service->getIndex());
    }

    public function testFetchAll()
    {
        $service = $this->createService();

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
        $service = $this->createService(true);

        $this->assertInstanceOf('\stdClass', $service->fetchAll());
    }

    public function testFetchYear()
    {
        $service = $this->createService();

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
        $service = $this->createService(true);

        $this->assertInstanceOf('\stdClass', $service->fetchYears());
    }

    public function testUpdate()
    {
        $service = $this->createService();

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
        $service = $this->createService(true);

        $service->update(1, []);
    }


    public function testCreate()
    {
        $service = $this->createService();

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
        $service = $this->createService(true);

        $service->create([]);
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

    protected function getServiceClass()
    {
        return Anaegjuvogin::class;
    }
}
