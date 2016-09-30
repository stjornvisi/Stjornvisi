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
/**
 * Class ArticleTest
 *
 * @package Stjornvisi\Service
 * @coversDefaultClass \Stjornvisi\Service\Company
 */
class CompanyTest extends AbstractServiceTest
{
    /**
     * Get one item, should return FALSE if item not found
     */
    public function testGet()
    {
        $service = $this->createService();
        $result = $service->get(1);
        $this->assertInstanceOf('\stdClass', $result);

        $result = $service->get(100);
        $this->assertFalse($result);
    }

    /**
     * Test get company by SSN
     */
    public function testGetBySsn()
    {
        $service = $this->createService();

        $result = $service->getBySsn('1234567890');
        $this->assertInstanceOf('\stdClass', $result);

        $result = $service->getBySsn('0000000000');
        $this->assertFalse($result);
    }

    /**
     * Test get company SSN exception
     * @expectedException Exception
     */
    public function testGetBySsnException()
    {
        $service = $this->createService(true);
        $service->getBySsn('1234567890');
    }

    /**
     * Should throe exception if service can't
     * connect to storage.
     * @expectedException Exception
     */
    public function testGetException()
    {
        $service = $this->createService(true);
        $service->get(1);
    }

    /**
     * Get all companies and counting results,
     * trying to filter out companies my business_type.
     */
    public function testFetchAll()
    {
        $service = $this->createService();
        $result = $service->fetchAll();
        $this->assertCount(4, $result);

        $result = $service->fetchAll(array('hf'));
        $this->assertCount(2, $result);

        $result = $service->fetchAll(array('sf','ohf'));
        $this->assertCount(2, $result);

        $result = $service->fetchAll(array('sf','ohf','hf'));
        $this->assertCount(0, $result);
    }

    /**
     * Get company by type test
     */
    public function testFetchType()
    {
        $service = $this->createService();

        $result = $service->fetchType();
        $this->assertEquals(4, count($result));
        $this->assertInternalType('array', $result);

        $result = $service->fetchType(['hf']);
        $this->assertEquals(2, count($result));

        $result = $service->fetchType(['hundur']);
        $this->assertEquals(0, count($result));

    }

    /**
     * @expectedException Exception
     */
    public function testFetchAllException()
    {
        $service = $this->createService(true);
        $service->fetchAll();
    }

    /**
     * Set new role for employee.
     */
    public function testSetEmployeeRole()
    {
        $service = $this->createService();

        $result = $service->setEmployeeRole(1, 1, 1);
        $this->assertEquals(1, $result);

        $result = $service->setEmployeeRole(1, 1, 1);
        $this->assertEquals(0, $result);

        $result = $service->setEmployeeRole(1, 3, 1);
        $this->assertEquals(0, $result);
    }

    /**
     * @expectedException Exception
     */
    public function testSetEmployeeRoleException()
    {
        $service = $this->createService(true);
        $service->setEmployeeRole(1, 1, 1);
    }

    /**
     * Try to promote user to a role
     * that is not allowed.
     * @expectedException Exception
     */
    public function testSetEmployeeRoleExceptionInvalidRoleType()
    {
        $service = $this->createService(true);
        $service->setEmployeeRole(1, 1, 10);
    }

    /**
     * Get all companies of user.
     * this is usually only one.
     */
    public function testGetByUser()
    {
        $service = $this->createService();

        $result = $service->getByUser(1);
        $this->assertCount(1, $result);

        $result = $service->getByUser(2);
        $this->assertCount(2, $result);

        $result = $service->getByUser(10);
        $this->assertCount(0, $result);
    }

    /**
     * Get all companies of user
     * without storage connection
     * @expectedException Exception
     */
    public function testGetByUserException()
    {
        $service = $this->createService(true);

        $service->getByUser(1);
    }

    /**
     * Test update company.
     * One can update a company that does not exists.
     */
    public function testUpdate()
    {
        $service = $this->createService();

        $result = $service->update(1, [
            'submit' => 'submit',
            'name' => 'n33',
            'ssn' => '1029384756',
            'address' => 'a1',
            'zip' => '3124',
            'website' => null,
            'number_of_employees' => '+200',
            'safe_name' => 'sn1',
            'created' => date('Y-m-d H:i:s')
        ]);
        $this->assertEquals(1, $result);

        $result = $service->update(10, [
            'submit' => 'submit',
            'name' => 'n33',
            'ssn' => '1029384756',
            'address' => 'a1',
            'zip' => '3124',
            'website' => null,
            'number_of_employees' => '+200',
            'safe_name' => 'sn1',
            'created' => date('Y-m-d H:i:s')
        ]);
        $this->assertEquals(0, $result);
    }

    /**
     * Test update company.
     * With invalid data
     * @expectedException Exception
     */
    public function testUpdateInvalid()
    {
        $service = $this->createService();
        $result = $service->update(1, [
            'submit' => 'submit',
            'gaman' => 'n33',
            'ssn' => '1029384756',
            'address' => 'a1',
            'zip' => '3124',
            'website' => null,
            'number_of_employees' => '+200',
            'safe_name' => 'sn1',
            'created' => date('Y-m-d H:i:s')
        ]);
        $this->assertEquals(1, $result);
    }

    /**
     * Update without storage connection.
     * @expectedException Exception
     */
    public function testUpdateException()
    {
        $service = $this->createService(true);

        $service->update(1, [
            'submit' => 'submit',
            'name' => 'n33',
            'ssn' => '1029384756',
            'address' => 'a1',
            'zip' => '3124',
            'website' => null,
            'number_of_employees' => '+200',
            'safe_name' => 'sn1',
            'created' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Test create company.
     */
    public function testCreate()
    {
        $service = $this->createService();

        $result = $service->create([
            'submit' => 'submit',
            'name' => 'n33',
            'ssn' => '1029384756',
            'address' => 'a1',
            'zip' => '3124',
            'website' => null,
            'number_of_employees' => '+200',
            'safe_name' => 'sn1',
            'created' => date('Y-m-d H:i:s')
        ]);
        $this->assertGreaterThan(4, $result);
    }

    /**
     * Test update company.
     * With invalid data
     * @expectedException Exception
     */
    public function testCreateInvalid()
    {
        $service = $this->createService();
        $service->create([
            'submit' => 'submit',
            'gaman' => 'n33',
            'ssn' => '1029384756',
            'address' => 'a1',
            'zip' => '3124',
            'website' => null,
            'number_of_employees' => '+200',
            'safe_name' => 'sn1',
            'created' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update without storage connection.
     * @expectedException Exception
     */
    public function testCreateException()
    {
        $service = $this->createService(true);

        $service->create([
            'submit' => 'submit',
            'name' => 'n33',
            'ssn' => '1029384756',
            'address' => 'a1',
            'zip' => '3124',
            'website' => null,
            'number_of_employees' => '+200',
            'safe_name' => 'sn1',
            'created' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Add user to company
     */
    public function testAddUser()
    {
        $service = $this->createService();

        $count = $service->addUser(2, 1, 1);
        $this->assertEquals(1, $count);
    }

    /**
     * Add user to company which is already
     * connected
     * @expectedException Exception
     */
    public function testAddUserAlreadyConnected()
    {
        $service = $this->createService();

        $count = $service->addUser(1, 1, 1);
        $this->assertEquals(1, $count);
    }

    /**
     * Add user to company which is already
     * connected
     * @expectedException Exception
     */
    public function testAddUserCompanyDoesNotExist()
    {
        $service = $this->createService();

        $service->addUser(10, 10, 1);
    }

    /**
     * Delete company from file.
     */
    public function testDelete()
    {
        $service = $this->createService();

        $count = $service->delete(1);
        $this->assertEquals(1, $count);

        $count = $service->delete(10);
        $this->assertEquals(0, $count);
    }

    /**
     * Delete company with no storage connection.
     * @expectedException Exception
     */
    public function testDeleteException()
    {
        $service = $this->createService(true);

        $service->delete(1);
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
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

    protected function getServiceClass()
    {
        return Company::class;
    }
}
