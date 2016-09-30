<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/4/14
 * Time: 11:17 AM
 */

namespace Stjornvisi\Service;

use Stjornvisi\ArrayDataSet;

require_once 'AbstractServiceTest.php';
class PageTest extends AbstractServiceTest
{
    /**
     * Get page.
     * 'Page Not Found' will return FALSE
     */
    public function testGet()
    {
        $service = $this->createService();

        $result = $service->get('/category/page1');
        $this->assertInstanceOf('\stdClass', $result);

        $result = $service->get('hundur');
        $this->assertFalse($result);


        $result = $service->getObject(1);
        $this->assertInstanceOf('\stdClass', $result);

        $result = $service->getObject(100);
        $this->assertFalse($result);
    }

    /**
     * Try to get page with no
     * storage connection
     * @expectedException Exception
     */
    public function testGetException()
    {
        $service = $this->createService(true);

        $service->get('/category/page1');
    }

    /**
     * Try to get page with no
     * storage connection
     * @expectedException Exception
     */
    public function testGetObjectException()
    {
        $service = $this->createService(true);

        $service->getObject(1);
    }

    /**
     * Update success.
     */
    public function testUpdate()
    {
        $service = $this->createService();
        $count = $service->update(1, [
            'submit' => 'submit',
            'label' => 'l1',
            'body' => 'b1',
        ]);
        $this->assertEquals(1, $count);
    }

    /**
     * Update invalid date.
     * @expectedException Exception
     */
    public function testUpdateInvalidDate()
    {
        $service = $this->createService();
        $service->update(1, [
            'submit' => 'submit',
            'label' => 'l1',
            'hundur' => 'b1',
        ]);
    }

    /**
     * Update invalid date.
     */
    public function testUpdateEntryNotFound()
    {
        $service = $this->createService();
        $count = $service->update(100, [
            'submit' => 'submit',
            'label' => 'l1',
            'body' => 'b1',
        ]);
        $this->assertEquals(0, $count);
    }


    /**
     * Update, no connection
     * @expectedException Exception
     */
    public function testUpdateNoConnection()
    {
        $service = $this->createService(true);

        $count = $service->update(100, [
            'submit' => 'submit',
            'label' => 'l1',
            'body' => 'b1',
        ]);
        $this->assertEquals(0, $count);
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'User' => [
                ['id'=>1, 'name'=>'', 'passwd'=>'', 'email'=>'one@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>1],
            ],
            'Page' => [
                ['id'=>1,'label'=>'/category/page1','body'=>'b1','created'=>date('Y-m-d H:i:s'),'affected'=>date('Y-m-d H:i:s'),'editor_id'=>1],
                ['id'=>2,'label'=>'/category/page2','body'=>'b1','created'=>date('Y-m-d H:i:s'),'affected'=>date('Y-m-d H:i:s'),'editor_id'=>1],
                ['id'=>3,'label'=>'/category/page3','body'=>'b1','created'=>date('Y-m-d H:i:s'),'affected'=>date('Y-m-d H:i:s'),'editor_id'=>1],
            ],
        ]);
    }

    protected function getServiceClass()
    {
        return Page::class;
    }
}
