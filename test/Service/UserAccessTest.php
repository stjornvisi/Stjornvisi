<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 22/03/14
 * Time: 19:50
 */

namespace Stjornvisi\Service;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\DataHelper;

require_once 'AbstractServiceTest.php';
class UserAccessTest extends AbstractServiceTest
{
    /**
     * Get type of user, i.e. if he
     * is admin or not.
     */
    public function testGetType()
    {
        $service = $this->createService();

        $result = $service->getType(1);
        $this->assertInstanceOf('\stdClass', $result, 'User exists|Result type is stdClass');
        $this->assertEquals(1, $result->is_admin, 'User exists|User is admin');
        $this->assertEquals(0, $result->type, 'User exists|Type property is ambiguous, but SHOULD be 0');

        $result = $service->getType(2);
        $this->assertInstanceOf('\stdClass', $result, 'User exists|Result type is stdClass');
        $this->assertEquals(0, $result->is_admin, 'User exists|User is not admin');
        $this->assertEquals(0, $result->type, 'User exists|Type property is ambiguous, but SHOULD be 0');

        $result = $service->getType(20);
        $this->assertInstanceOf('\stdClass', $result, 'User not exists|Result type is stdClass');
        $this->assertEquals(0, $result->is_admin, 'User not exists|User is not admin');
        $this->assertEquals(0, $result->type, 'User not exists|Type property is ambiguous, but SHOULD be 0');

        $result = $service->getType(null);
        $this->assertInstanceOf('\stdClass', $result, 'User null|Result type is stdClass');
        $this->assertEquals(0, $result->is_admin, 'User null|User is not admin');
        $this->assertEquals(0, $result->type, 'User null|Type property is ambiguous, but SHOULD be 0');
    }

    /**
     * Try to get type of user with no
     * storage connection
     * @expectedException Exception
     */
    public function testGetTypeException()
    {
        $service = $this->createService(true);

        $service->getType(1);
    }

    /**
     * Test if 'not' logged in user has access
     * in relation to groups, which he never has.
     */
    public function testGetTypeByGroupAnonymousUser()
    {
        $service = $this->createService();

        $result = $service->getTypeByGroup(null, []);
        $this->assertFalse($result->is_admin);
        $this->assertNull($result->type);

        $result = $service->getTypeByGroup(null, [1,2,3]);
        $this->assertFalse($result->is_admin);
        $this->assertNull($result->type);
    }

    /**
     * Test if 'not' logged in user has access
     * when there is no connection. Since user
     * with ID = null, will never have any access, this
     * method will not access storage and will not
     * throw an exception
     */
    public function testGetTypeByGroupAnonymousUserException()
    {
        $service = $this->createService(true);

        $result = $service->getTypeByGroup(null, []);
        $this->assertInstanceOf('\stdClass', $result);
    }

    /**
     * Get type of user in relation to groups,
     * where the group array is empty. Here we are
     * basically checking if the user is admin 'cause
     * user can never have access to group that checking
     * against :)
     */
    public function testGetTypeByGroupWithEmptyGroupArray()
    {
        $service = $this->createService();

        $result = $service->getTypeByGroup(1, []);
        $this->assertEquals(1, $result->is_admin, 'User is admin');
        $this->assertNull($result->type, 'User is admin');

        $result = $service->getTypeByGroup(2, []);
        $this->assertEquals(0, $result->is_admin, 'User is not admin');
        $this->assertNull($result->type, 'User is not admin');

        $result = $service->getTypeByGroup(200, []);
        $this->assertEquals(0, $result->is_admin, 'User does not exists');
        $this->assertNull($result->type, 'User does not exists');
    }

    /**
     * Get type of user in relation to groups.
     */
    public function testGetTypeByGroup()
    {
        $service = $this->createService();

        $result = $service->getTypeByGroup(1, [1]);
        $this->assertEquals(1, $result->is_admin);
        $this->assertEquals(0, $result->type, 'User has no access to group');

        $result = $service->getTypeByGroup(2, [1]);
        $this->assertEquals(0, $result->is_admin);
        $this->assertEquals(1, $result->type, 'User is manager of group');

        $result = $service->getTypeByGroup(2, [2]);
        $this->assertEquals(0, $result->is_admin);
        $this->assertEquals(2, $result->type, 'User is chairman of group');

        $result = $service->getTypeByGroup(2, [1,2]);
        $this->assertEquals(0, $result->is_admin);
        $this->assertEquals(2, $result->type, 'User is manager of 1, but chairman of 2');

        $result = $service->getTypeByGroup(2, [2,1,2]);
        $this->assertEquals(0, $result->is_admin);
        $this->assertEquals(2, $result->type, 'User is manager of 1, but chairman of 2 (reverse)');

        $result = $service->getTypeByGroup(2, [3,4]);
        $this->assertEquals(0, $result->is_admin);
        $this->assertNull($result->type, 'User has no access to 3 and 4');

        $result = $service->getTypeByGroup(2, [4,3,4]);
        $this->assertEquals(0, $result->is_admin);
        $this->assertNull($result->type, 'User has no access to 3 and 4 (reverse)');

        $result = $service->getTypeByGroup(2, [4,3,null]);
        $this->assertEquals(0, $result->is_admin);
        $this->assertNull($result->type, 'User has no access to 3 and 4 (reverse with null)');

        $result = $service->getTypeByGroup(2, 4);
        $this->assertEquals(0, $result->is_admin);
        $this->assertNull($result->type, 'User has no access to 4');

        $result = $service->getTypeByGroup(2, 1);
        $this->assertEquals(0, $result->is_admin);
        $this->assertEquals(1, $result->type, 'User has access to 1');

        $result = $service->getTypeByGroup(2, 2);
        $this->assertEquals(0, $result->is_admin);
        $this->assertEquals(2, $result->type, 'User has access to 2');
    }

    /**
     * @expectedException Exception
     */
    public function testGetTypeByGroupException()
    {
        $service = $this->createService(true);

        $service->getTypeByGroup(1, [1,2]);
    }

    /**
     * Test if anonymous user has access to company
     */
    public function testGetTypeByCompanyAnonymousUser()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(null, 1);
        $this->assertEquals(0, $result->is_admin);
        $this->assertNull($result->type);

        $result = $service->getTypeByCompany(null, null);
        $this->assertEquals(0, $result->is_admin);
        $this->assertNull($result->type);
    }

    /**
     * Test user access to company.
     */
    public function testGetTypeByCompany()
    {
        $service = $this->createService();

        $result = $service->getTypeByCompany(1, 1);
        $this->assertEquals(0, $result->type, 'User in company, not key_user');

        $result = $service->getTypeByCompany(2, 1);
        $this->assertEquals(1, $result->type, 'User in company, key_user');

        $result = $service->getTypeByCompany(1, 2);
        $this->assertNull($result->type, 'User not in company');

        $result = $service->getTypeByCompany(100, 2);
        $this->assertNull($result->type, 'User does not exists');

        $result = $service->getTypeByCompany(1, 200);
        $this->assertNull($result->type, 'Company does not exists');

        $result = $service->getTypeByCompany(100, 200);
        $this->assertNull($result->type, 'Company and user does not exists');
    }

    /**
     * Test connection from user to company
     * when there is no connection to storage.
     * @expectedException Exception
     */
    public function testGetTypeByCompanyException()
    {
        $service = $this->createService(true);
        $service->getTypeByCompany(1, 1);
    }

    /**
     * Test for user has access to user.
     */
    public function testGetTypeByUser()
    {
        $service = $this->createService();

        $result = $service->getTypeByUser(null, null);
        $this->assertEquals(0, $result->is_admin);
        $this->assertEquals(0, $result->type);

        $result = $service->getTypeByUser(2, 1);
        $this->assertEquals(1, $result->is_admin);
        $this->assertEquals(0, $result->type);

        $result = $service->getTypeByUser(2, 2);
        $this->assertEquals(0, $result->is_admin);
        $this->assertEquals(1, $result->type);
    }

    /**
     * Test for user has access to user
     * when there is no connection to storage.
     * @expectedException Exception
     */
    public function testGetTypeByUserException()
    {
        $service = $this->createService(true);
        $service->getTypeByUser(2, 1);
    }

    /**
     * Change type of user.
     */
    public function testSetType()
    {
        $service = $this->createService();

        $result = $service->setType(2, 1);
        $this->assertEquals(1, $result);
    }

    /**
     * Try to change type of user
     * when there is no connection to
     * storage.
     * @expectedException Exception
     */
    public function testSetTypeException()
    {
        $service = $this->createService(true);

        $service->setType(2, 1);
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'User' => [
                ['id'=>1, 'name'=>'n1', 'passwd'=>'p1', 'email'=>'one@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>1],
                ['id'=>2, 'name'=>'n2', 'passwd'=>'p2', 'email'=>'two@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>3, 'name'=>'n3', 'passwd'=>'p3', 'email'=>'thr@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>4, 'name'=>'n4', 'passwd'=>'p4', 'email'=>'fou@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>5, 'name'=>'n5', 'passwd'=>'p5', 'email'=>'fiv@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>6, 'name'=>'n6', 'passwd'=>'p6', 'email'=>'six@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
            ],
            'Group' => [
                DataHelper::newGroup(1),
                DataHelper::newGroup(2),
                DataHelper::newGroup(3),
                DataHelper::newGroup(4),
                DataHelper::newGroup(5),
                DataHelper::newGroup(6),
            ],
            'Group_has_User' => [
                ['group_id'=>1,'user_id'=>1,'type'=>0],
                ['group_id'=>1,'user_id'=>2,'type'=>1],
                ['group_id'=>2,'user_id'=>2,'type'=>2],
            ],
            'Company' => [
                ['id'=>1,'name'=>'n1','ssn'=>'1234567891','address'=>'a1','zip'=>'1','website'=>null,'number_of_employees'=>'','business_type'=>'hf','safe_name'=>'s1','created'=>date('Y-m-d H:i:s')],
                ['id'=>2,'name'=>'n2','ssn'=>'1234567892','address'=>'a2','zip'=>'1','website'=>null,'number_of_employees'=>'','business_type'=>'hf','safe_name'=>'s2','created'=>date('Y-m-d H:i:s')],
                ['id'=>3,'name'=>'n3','ssn'=>'1234567893','address'=>'a3','zip'=>'1','website'=>null,'number_of_employees'=>'','business_type'=>'hf','safe_name'=>'s3','created'=>date('Y-m-d H:i:s')],
            ],
            'Company_has_User' => [
                ['user_id'=>1,'company_id'=>1,'key_user'=>0],
                ['user_id'=>2,'company_id'=>1,'key_user'=>1],
            ],
        ]);
    }

    protected function getServiceClass()
    {
        return User::class;
    }
}
