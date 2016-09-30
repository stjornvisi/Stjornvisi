<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/22/14
 * Time: 8:38 PM
 */

namespace Stjornvisi\Service;

use Stjornvisi\ArrayDataSet;

require_once 'AbstractServiceTest.php';
class UserTest extends AbstractServiceTest
{
    /**
     * Try to get user when there is
     * no connection to storage.
     * @expectedException Exception
     */
    public function testGetException()
    {
        $service = $this->createService(true);

        $service->get(1);
    }

    /**
     * Get user by ID and email,
     * both valid IDs and emails as well
     * as invalid values (which should return FALSE)
     */
    public function testGet()
    {
        $service = $this->createService();

        $result = $service->get(1);
        $this->assertEquals('n1@mail.com', $result->email);

        $result = $service->get('n1@mail.com');
        $this->assertEquals('n1@mail.com', $result->email);

        $result = $service->get(100);
        $this->assertFalse($result);

        $result = $service->get('one@mail123.com');
        $this->assertFalse($result);
    }

    /**
     * Get all users
     */
    public function testFetchAll()
    {
        $service = $this->createService();

        $result = $service->fetchAll();
        $this->assertCount(8, $result);
    }

    /**
     * Get all users when there is no
     * storage connection
     * @expectedException Exception
     */
    public function testFetchAllException()
    {
        $service = $this->createService(true);

        $service->fetchAll();
    }

    public function testGetByGroup()
    {
        $service = $this->createService();

        $this->assertEquals(7, count($service->getByGroup(5, null)));
        $this->assertEquals(2, count($service->getByGroup(5, 2)));
        $this->assertEquals(3, count($service->getByGroup(5, 1)));
        $this->assertEquals(2, count($service->getByGroup(5, 0)));
    }

    /**
     * @expectedException Exception
     */
    public function testGetByGroupException()
    {
        $service = $this->createService(true);

        $this->assertEquals(7, count($service->getByGroup(5, null)));
    }

    public function testGetTypeByGroupArray()
    {
        $service = $this->createService();

        $d1 = $service->getTypeByGroup(3, [5,2]);
        $this->assertEquals(1, $d1->type);
        $d1 = $service->getTypeByGroup(3, [2,5]);
        $this->assertEquals(1, $d1->type);

        $d2 = $service->getTypeByGroup(1, [1,5,2]);
        $this->assertEquals(2, $d2->type);
        $d2 = $service->getTypeByGroup(1, [2,1,5]);
        $this->assertEquals(2, $d2->type);

        $d3 = $service->getTypeByGroup(8, [1,5,2]);
        $this->assertNull($d3->type);
        $d3 = $service->getTypeByGroup(8, [5,2,1]);
        $this->assertNull($d3->type);

        $d4 = $service->getTypeByGroup(5, [1,2,5]);
        $this->assertEquals(1, $d4->type);
        $d4 = $service->getTypeByGroup(5, [2,1,5]);
        $this->assertEquals(1, $d4->type);

        $d5 = $service->getTypeByGroup(null, [2,1,5]);
        $this->assertNull($d5->type);
    }

    public function testGetTypeByGroupNullUser()
    {
        $service = $this->createService();

        $result = $service->getTypeByGroup(null, 1);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('is_admin', $result);
        $this->assertObjectHasAttribute('type', $result);
        $this->assertEquals(false, $result->is_admin);
        $this->assertEquals(null, $result->type);
    }

    public function testGetTypeByGroupNullGroup()
    {
        $service = $this->createService();

        $result = $service->getTypeByGroup(1, null);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('is_admin', $result);
        $this->assertObjectHasAttribute('type', $result);
        $this->assertEquals(true, (bool)$result->is_admin);
        $this->assertEquals(null, $result->type);


        $service = $this->createService();

        $result = $service->getTypeByGroup(2, null);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('is_admin', $result);
        $this->assertObjectHasAttribute('type', $result);
        $this->assertEquals(false, (bool)$result->is_admin);
        $this->assertEquals(null, $result->type);
    }

    public function testGetTypeByGroupUserInGroup()
    {
        $service = $this->createService();

        $result = $service->getTypeByGroup(1, 1);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('is_admin', $result);
        $this->assertObjectHasAttribute('type', $result);
        $this->assertEquals(true, (bool)$result->is_admin);
        $this->assertEquals(2, $result->type);

        $result = $service->getTypeByGroup(1, 2);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('is_admin', $result);
        $this->assertObjectHasAttribute('type', $result);
        $this->assertEquals(true, (bool)$result->is_admin);
        $this->assertEquals(1, $result->type);

        $result = $service->getTypeByGroup(2, 2);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('is_admin', $result);
        $this->assertObjectHasAttribute('type', $result);
        $this->assertEquals(false, (bool)$result->is_admin);
        $this->assertEquals(0, $result->type);
    }

    public function testGetTypeByGroupUserNotInGroup()
    {
        $service = $this->createService();

        $result = $service->getTypeByGroup(3, 4);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('is_admin', $result);
        $this->assertObjectHasAttribute('type', $result);
        $this->assertEquals(false, (bool)$result->is_admin);
        $this->assertEquals(null, $result->type);

    }

    public function testGetType()
    {
        $service = $this->createService();

        $user1 = $service->getType(1);
        $user2 = $service->getType(2);
        $user3 = $service->getType(100);
        $user4 = $service->getType(null);

        $this->assertTrue($user1->is_admin, 'Exists, is admin');
        $this->assertFalse($user2->is_admin, 'Exists, is not admin');
        $this->assertFalse($user3->is_admin, 'Does not exists');
        $this->assertFalse($user4->is_admin, 'ID is null');
    }


    /**
     * Set password for found user as well
     * as for one that does not exists.
     */
    public function testSetPassword()
    {
        $service = $this->createService();

        $result = $service->setPassword(1, 'hundur');
        $this->assertEquals(1, $result);

        $result = $service->setPassword(100, 'hundur');
        $this->assertEquals(0, $result);
    }

    /**
     * Set password for user then there is no
     * connection to storage.
     * @expectedException Exception
     */
    public function testSetPasswordException()
    {
        $service = $this->createService(true);

        $service->setPassword(1, 'hundur');
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet(include __DIR__.'/../data/user.01.php');
    }

    protected function getServiceClass()
    {
        return User::class;
    }
}
