<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/4/14
 * Time: 11:17 AM
 */

namespace Stjornvisi\Service;

use PDO;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\DatabaseTestCase;
use Stjornvisi\DataHelper;
use Stjornvisi\PDOMock;
use Stjornvisi\Bootstrap;

/**
 * Class ArticleTest
 *
 * @package Stjornvisi\Service
 * @coversDefaultClass \Stjornvisi\Service\Article
 */
class GroupTest extends DatabaseTestCase
{
    /**
     * Test get.
     *
     * Should return stdClass if successful,
     * else false if entry not found.
     */
    public function testGet()
    {
        $service = $this->createService();

        $group1 = $service->get(1);
        $this->assertInstanceOf('\stdClass', $group1);

        $group2 = $service->get('n1');
        $this->assertInstanceOf('\stdClass', $group2);

        $group3 = $service->get(100);
        $this->assertFalse($group3);
    }

    /**
     * @expectedException Exception
     */
    public function testGetException()
    {
        $service = $this->createService(new PDOMock());
        $service->get(1);
    }

    /**
     * Get first year
     */
    public function testGetFirstYear()
    {
        $service = $this->createService();

        $group1 = $service->getFirstYear(1);
        $this->assertInternalType('int', $group1);


        $group3 = $service->getFirstYear(100);
        $this->assertInternalType('int', $group3);
    }

    /**
     * @expectedException Exception
     */
    public function testGetFirstYearException()
    {
        $service = $this->createService(new PDOMock());
        $service->getFirstYear(1);
    }

    /**
     *
     */
    public function testRegisterUser()
    {
        $service = $this->createService();

        $result = $service->registerUser(1, 1, true);
        $this->assertInternalType('int', $result);

        $result = $service->registerUser(1, 1, false);
        $this->assertInternalType('int', $result);
    }

    /**
     * @expectedException Exception
     */
    public function testRegisterUserExceptionTrue()
    {
        $service = $this->createService(new PDOMock());
        $service->registerUser(1, 1, true);
    }

    /**
     * @expectedException Exception
     */
    public function testRegisterUserExceptionFalse()
    {
        $service = $this->createService(new PDOMock());
        $service->registerUser(1, 1, false);
    }


    /**
     * Test get all groups by user.
     */
    public function testGetByUser()
    {
        $service = $this->createService();

        $group1 = $service->getByUser(1);
        $this->assertInternalType('array', $group1);
    }

    /**
     * @expectedException Exception
     */
    public function testGetByUserException()
    {
        $service = $this->createService(new PDOMock());
        $service->getByUser(1);
    }

    public function testGetVisibleForAnySucceeds()
    {
        Bootstrap::authenticateUser(0);
        $service = $this->createService();
        $data = $service->get(1);
        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertEquals(1, $data->id);
    }

    public function testGetHiddenForAnonymousFails()
    {
        Bootstrap::authenticateUser(0);
        $service = $this->createService();
        $data = $service->get(4);
        $this->assertEmpty($data);
    }

    public function testGetHiddenForMemberFails()
    {
        Bootstrap::authenticateUser(4, 0); // 4 is a normal member in group 3
        $service = $this->createService();
        $data = $service->get(3);
        $this->assertEmpty($data);
    }

    public function testGetHiddenForLeaderSucceeds()
    {
        Bootstrap::authenticateUser(2, 0); // 2 is leader in group 3
        $service = $this->createService();
        $data = $service->get(3);
        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertEquals(3, $data->id);
    }

    public function testGetHiddenForOtherLeaderFails()
    {
        Bootstrap::authenticateUser(2, 0); // 2 is leader in group 3
        $service = $this->createService();
        $data = $service->get(4);
        $this->assertEmpty($data);
    }

    public function testGetHiddenForChairmanSucceeds()
    {
        Bootstrap::authenticateUser(3, 0); // 3 is Chairman in group 3
        $service = $this->createService();
        $data = $service->get(3);
        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertEquals(3, $data->id);
    }

    public function testGetHiddenForAdminSucceeds()
    {
        Bootstrap::authenticateUser(1, 1);
        $service = $this->createService();
        $data = $service->get(4);
        $this->assertInstanceOf(\stdClass::class, $data);
        $this->assertEquals(4, $data->id);
    }

    public function testFetchDetailsFetchesAllForAdmin()
    {
        Bootstrap::authenticateUser(1, 1);
        $service = $this->createService();
        $data = $service->fetchDetails();
        $this->assertCount(4, $data);
    }

    public function testFetchDetailsFetchesAllNonHiddenForAnonymous()
    {
        Bootstrap::authenticateUser(0);
        $service = $this->createService();
        $data = $service->fetchDetails();
        $this->assertCount(2, $data);
    }

    public function testFetchDetailsFetchesAllNonHiddenForMembers()
    {
        Bootstrap::authenticateUser(4, 0); // 4 is a normal member in group 3
        $service = $this->createService();
        $data = $service->fetchDetails();
        $this->assertCount(2, $data);
    }

    public function testFetchDetailsFetchesAllNonHiddenForLeaders()
    {
        Bootstrap::authenticateUser(2, 0); // 2 is leader in group 3
        $service = $this->createService();
        $data = $service->fetchDetails();
        $this->assertCount(3, $data);
    }

    public function testFetchDetailsFetchesAllNonHiddenForChairmen()
    {
        Bootstrap::authenticateUser(3, 0); // 3 is Chairman in group 3
        $service = $this->createService();
        $data = $service->fetchDetails();
        $this->assertCount(3, $data);
    }

    public function testFetchAllFetchesAllForAdmin()
    {
        Bootstrap::authenticateUser(1, 1);
        $service = $this->createService();
        $data = $service->fetchAll();
        $this->assertCount(4, $data);
    }

    public function testFetchAllFetchesAllNonHiddenForAnonymous()
    {
        Bootstrap::authenticateUser(0);
        $service = $this->createService();
        $data = $service->fetchAll();
        $this->assertCount(2, $data);
    }

    public function testFetchAllFetchesAllNonHiddenForMembers()
    {
        Bootstrap::authenticateUser(4, 0); // 4 is a normal member in group 3
        $service = $this->createService();
        $data = $service->fetchAll();
        $this->assertCount(2, $data);
    }

    public function testFetchAllFetchesAllNonHiddenForLeaders()
    {
        Bootstrap::authenticateUser(2, 0); // 2 is leader in group 3
        $service = $this->createService();
        $data = $service->fetchAll();
        $this->assertCount(3, $data);
    }

    public function testFetchAllFetchesAllNonHiddenForChairmen()
    {
        Bootstrap::authenticateUser(3, 0); // 3 is Chairman in group 3
        $service = $this->createService();
        $data = $service->fetchAll();
        $this->assertCount(3, $data);
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'Group' => [
                DataHelper::newGroup(1),
                DataHelper::newGroup(2),
                DataHelper::newGroup(3, 1),
                DataHelper::newGroup(4, 1),
            ],
            'Company' => [
                DataHelper::newCompany(1),
            ],
            'User' => [
                DataHelper::newUser(1, 1),
                DataHelper::newUser(2),
                DataHelper::newUser(3),
                DataHelper::newUser(4),
            ],
            'Company_has_User' => [
                DataHelper::newCompanyHasUser(1, 1),
                DataHelper::newCompanyHasUser(2, 1),
                DataHelper::newCompanyHasUser(3, 1),
                DataHelper::newCompanyHasUser(4, 1),
            ],
            'Event' => DataHelper::newEventSeries(),
            'Group_has_User' => [
                DataHelper::newGroupHasUser(3, 2, 1),
                DataHelper::newGroupHasUser(3, 3, 2),
                DataHelper::newGroupHasUser(3, 4, 0),
            ],
            'Group_has_Event' => [
                DataHelper::newGroupHasEvent(2, 1, 0),
                DataHelper::newGroupHasEvent(2, 2, 0),
                DataHelper::newGroupHasEvent(2, 3, 0),
                DataHelper::newGroupHasEvent(3, 2, 0),
                DataHelper::newGroupHasEvent(4, null, 0),
            ],
        ]);
    }

    /**
     * @param null|bool|PDO $dataSource
     *
     * @return Group
     */
    protected function createService($dataSource = null)
    {
        $sm = Bootstrap::getServiceManager();
        $service = $sm->get(Group::class);
        if ($dataSource) {
            $service->setDataSource($dataSource);
        }
        else {
            // Must reset the data source to the original one
            $service->setDataSource($sm->get(PDO::class));
        }
        return $service;
    }
}
