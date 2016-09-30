<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/5/14
 * Time: 10:25 AM
 */

namespace Stjornvisi\Service;

use Stjornvisi\ArrayDataSet;

require_once 'AbstractServiceTest.php';
class BoardeTest extends AbstractServiceTest
{
    /**
     * Test get single period.
     */
    public function testGetPeriod()
    {
        $service = $this->createService();
        $result1 = $service->getBoard('2013-2014');
        $result2 = $service->getBoard('2012-2013');
        $result3 = $service->getBoard('2011-2012');

        $this->assertInternalType('array', $result1);
        $this->assertInternalType('array', $result2);
        $this->assertInternalType('array', $result3);

        $this->assertEquals(3, count($result1));
        $this->assertEquals(2, count($result2));
        $this->assertEquals(0, count($result3));
    }

    /**
     * Test get single period with no connection
     * to the storage.
     * @expectedException Exception
     */
    public function testGetPeriodException()
    {
        $service = $this->createService(true);
        $service->getBoard('2012-2013');
    }

    /**
     * Get all periods containing all board members.
     */
    public function testGetBoardPeriods()
    {
        $service = $this->createService();
        $result = $service->getPeriods();
        $this->assertEquals(2, count($result));
    }

    /**
     * Test get all boards.
     */
    public function testGetBoards()
    {
        $service = $this->createService();
        $result = $service->getBoards();
        $this->assertCount(2, $result);
    }

    /**
     * Test get all boards with no
     * database connection.
     * @expectedException Exception
     */
    public function testGetBoardsException()
    {
        $service = $this->createService(true);
        $result = $service->getBoards();
        $this->assertCount(2, $result);
    }

    /**
     * Get all periods containing all board members
     * when there is no connection.
     * @expectedException Exception
     */
    public function testGetBoardPeriodsException()
    {
        $service = $this->createService(true);
        $service->getPeriods();
    }

    /**
     * Get all members on file.
     */
    public function testGetMembers()
    {
        $service = $this->createService();
        $result = $service->getMembers();
        $this->assertEquals(3, count($result));
    }

    /**
     * Get all members on file when
     * there is no connection.
     * @expectedException Exception
     */
    public function testGetMembersException()
    {
        $service = $this->createService(true);
        $service->getMembers();
    }

    /**
     * Get member on file that does not
     * exists as well as one that does.
     */
    public function testGetMember()
    {
        $service = $this->createService();
        $result = $service->getMember(1);
        $this->assertInstanceOf('\stdClass', $result);

        $result = $service->getMember(100);
        $this->assertFalse($result);
    }

    /**
     * Get member exception
     * @expectedException Exception
     */
    public function testGetMemberException()
    {
        $service = $this->createService(true);
        $service->getMember(1);
    }

    /**
     * Create one bord member
     */
    public function testCreateMember()
    {
        $service = $this->createService();
        $id = $service->createMember([
            'name' => 'n1',
            'email' => 'e1',
            'company' => 'c1',
            'avatar' => 'a1',
            'info' => 'i1',
            'submit' => 'submit'
        ]);
        $this->assertGreaterThan(3, $id);
    }

    /**
     * Create one bord member
     * @expectedException Exception
     */
    public function testCreateMemberInvalidDate()
    {
        $service = $this->createService();
        $id = $service->createMember([
            'hundur' => 'n1',
            'email' => 'e1',
            'company' => 'c1',
            'avatar' => 'a1',
            'info' => 'i1',
            'submit' => 'submit'
        ]);
        $this->assertGreaterThan(3, $id);
    }

    /**
     * Update one bord member
     */
    public function testUpdateMember()
    {
        $service = $this->createService();
        $count = $service->updateMember(1, [
            'name' => 'n1'.rand(0, 3),
            'email' => 'e1',
            'company' => 'c1',
            'avatar' => 'a1',
            'info' => 'i1',
            'submit' => 'submit'
        ]);
        $this->assertEquals(1, $count);

        $count = $service->updateMember(100, [
            'name' => 'n1'.rand(0, 3),
            'email' => 'e1',
            'company' => 'c1',
            'avatar' => 'a1',
            'info' => 'i1',
            'submit' => 'submit'
        ]);
        $this->assertEquals(0, $count);
    }

    /**
     * Update one bord member
     * @expectedException Exception
     */
    public function testUpdateMemberInvalidData()
    {
        $service = $this->createService();
        $count = $service->updateMember(1, [
            'hundur' => 'n1'.rand(0, 3),
            'email' => 'e1',
            'company' => 'c1',
            'avatar' => 'a1',
            'info' => 'i1',
            'submit' => 'submit'
        ]);
        $this->assertEquals(1, $count);
    }


    /**
     * Get how many terms ara available
     * from year 2000
     * 2000-2001
     * 2001-2002
     * ...etc
     */
    public function testGetTerms()
    {
        $service = $this->createService();
        $result = $service->getTerms();
        $this->assertGreaterThan(14, $result);
    }

    /**
     * Connect member on file with term.
     */
    public function testConnectMember()
    {
        $service = $this->createService();
        $count = $service->connectMember([
            'boardmember_id' => 1,
            'term' => '2001-2002',
            'is_chairman' => 1,
            'is_reserve' => 1,
            'is_manager' => 1
        ]);
        $this->assertEquals(1, $count);
    }

    /**
     * Connect member on file with term
     * but the member does not exist.
     * @expectedException Exception
     */
    public function testConnectMemberMemberNotFound()
    {
        $service = $this->createService();
        $count = $service->connectMember([
            'boardmember_id' => 100,
            'term' => '2001-2002',
            'is_chairman' => 1,
            'is_reserve' => 1,
            'is_manager' => 1
        ]);
        $this->assertEquals(1, $count);
    }

    /**
     * Disconnect member from term.
     * Both connection that exists and
     * one the does not
     */
    public function testDisconnectMember()
    {
        $service = $this->createService();
        $count = $service->disconnectMember(1);
        $this->assertEquals(1, $count);


        $count = $service->disconnectMember(100);
        $this->assertEquals(0, $count);
    }

    /**
     * Disconnect member from term.
     * @expectedException Exception
     */
    public function testDisconnectMemberException()
    {
        $service = $this->createService(true);
        $count = $service->disconnectMember(1);
        $this->assertEquals(1, $count);
    }

    /**
     * Get one connection from member to term.
     * If connection is not found, return FALSE.
     */
    public function testGetMemberConnection()
    {
        $service = $this->createService();
        $result = $service->getMemberConnection(1);
        $this->assertInstanceOf('\stdClass', $result);

        $result = $service->getMemberConnection(100);
        $this->assertFalse($result);
    }


    /**
     * Get one connection from member to term.
     * without storage connection.
     * @expectedException Exception
     */
    public function testGetMemberConnectionException()
    {
        $service = $this->createService(true);
        $service->getMemberConnection(1);
    }

    /**
     * Update member connection.
     * One should be able to update connection
     * that does not exist.
     */
    public function testUpdateMemberConnection()
    {
        $service = $this->createService();
        $count = $service->updateMemberConnection(1, [
            'boardmember_id' => 1,
            'term' => '2013-2014',
            'is_chairman' => 1,
            'is_reserve' => 1,
            'is_manager' => 1
        ]);
        $this->assertEquals(1, $count);


        $count = $service->updateMemberConnection(100, [
            'boardmember_id' => 1,
            'term' => '2013-2014',
            'is_chairman' => 1,
            'is_reserve' => 1,
            'is_manager' => 1
        ]);
        $this->assertEquals(0, $count);
    }

    /**
     * Update member connection.
     * One should be able to update connection
     * that does not exist.
     * @expectedException Exception
     */
    public function testUpdateMemberConnectionMemberNotFound()
    {
        $service = $this->createService();
        $count = $service->updateMemberConnection(1, [
            'boardmember_id' => 100,
            'term' => '2013-2014',
            'is_chairman' => 1,
            'is_reserve' => 1,
            'is_manager' => 1
        ]);
        $this->assertEquals(1, $count);
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'BoardMember' => [
                ['id'=>1,'name'=>'n1','email'=>'e@a.is','company'=>'c1','avatar'=>'a1','info'=>'i1'],
                ['id'=>2,'name'=>'n2','email'=>'e@b.is','company'=>'c2','avatar'=>'a2','info'=>'i2'],
                ['id'=>3,'name'=>'n3','email'=>'e@c.is','company'=>'c3','avatar'=>'a3','info'=>'i3'],
            ],
            'BoardMemberTerm' => [
                ['id'=>1,'boardmember_id'=>1,'term'=>'2013-2014','is_chairman'=>1,'is_reserve'=>0,'is_manager'=>0],
                ['id'=>2,'boardmember_id'=>2,'term'=>'2013-2014','is_chairman'=>0,'is_reserve'=>0,'is_manager'=>1],
                ['id'=>3,'boardmember_id'=>3,'term'=>'2013-2014','is_chairman'=>0,'is_reserve'=>0,'is_manager'=>0],
                ['id'=>4,'boardmember_id'=>1,'term'=>'2012-2013','is_chairman'=>1,'is_reserve'=>0,'is_manager'=>0],
                ['id'=>5,'boardmember_id'=>2,'term'=>'2012-2013','is_chairman'=>0,'is_reserve'=>0,'is_manager'=>1],
            ],
        ]);
    }

    protected function getServiceClass()
    {
        return Board::class;
    }
}
