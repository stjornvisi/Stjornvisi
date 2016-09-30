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

class EmailTest extends AbstractServiceTest
{
    public function testCreate()
    {
        $service = $this->createService();

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
        $service = $this->createService(true);

        $rowCount = $service->create([]);
        $this->assertInternalType('int', $rowCount);
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
        return Email::class;
    }
}
