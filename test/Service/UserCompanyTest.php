<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/17/14
 * Time: 3:37 PM
 */

namespace Stjornvisi\Service;

use Stjornvisi\ArrayDataSet;

require_once 'AbstractServiceTest.php';
class UserCompanyTest extends AbstractServiceTest
{
    public function testUserDoesNotExist()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(100, 1);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals(false, $result->is_admin);
        $this->assertNull($result->type);
    }

    public function testUserCompanyDoesNotExist()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(1, 100);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals(1, $result->is_admin);
        $this->assertNull($result->type);
    }

    public function testNullUserNullCompany()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(null, null);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals(false, $result->is_admin);
        $this->assertNull($result->type);
    }

    public function testNullUserActiveCompany()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(null, 1);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals(false, $result->is_admin);
        $this->assertNull($result->type);
    }

    public function testActiveUserNullCompany()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(1, null);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals(true, $result->is_admin);
        $this->assertNull($result->type);
    }

    public function testActiveUserActiveCompanyUserNotConnected()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(2, 2);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals(0, $result->is_admin, 'is not admin');
        $this->assertNull($result->type, 'access is of no type');
    }

    public function testActiveUserActiveCompanyUserConnected1()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(2, 1);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals(0, $result->is_admin, 'is not admin');
        $this->assertEquals(1, $result->type, 'access is of type 1');
    }

    public function testActiveUserActiveCompanyUserConnected2()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(3, 1);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertEquals(0, $result->is_admin, 'is not admin');
        $this->assertEquals(0, $result->type, 'access is of type 1');
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'User' => [
                ['id'=>1, 'name'=>'1', 'passwd'=>'1', 'email'=>'one@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>1],
                ['id'=>2, 'name'=>'2', 'passwd'=>'2', 'email'=>'two@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>3, 'name'=>'3', 'passwd'=>'3', 'email'=>'thr@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
            ],
            'Company' => [
                ['id'=>1,'name'=>'c1','ssn'=>'1234567890','address'=>'a1','zip'=>'101','website'=>null,'number_of_employees'=>'1','business_type'=>'hf','safe_name'=>'c1','created'=>date('Y-m-d H:i:s')],
                ['id'=>2,'name'=>'c2','ssn'=>'2134567890','address'=>'b1','zip'=>'101','website'=>null,'number_of_employees'=>'1','business_type'=>'hf','safe_name'=>'c2','created'=>date('Y-m-d H:i:s')],
                ['id'=>3,'name'=>'c3','ssn'=>'9134567890','address'=>'c1','zip'=>'101','website'=>null,'number_of_employees'=>'1','business_type'=>'hf','safe_name'=>'c3','created'=>date('Y-m-d H:i:s')],
            ],
            'Company_has_User' => [
                ['user_id' => 2, 'company_id'=> 1,'key_user'=> 1],
                ['user_id' => 3, 'company_id'=> 1,'key_user'=> 0],
            ],
        ]);
    }

    protected function getServiceClass()
    {
        return User::class;
    }
}
