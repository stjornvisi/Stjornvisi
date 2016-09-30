<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/22/14
 * Time: 3:46 PM
 */

namespace Stjornvisi\Service;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\DataHelper;

require_once 'AbstractServiceTest.php';
class UserAttendanceTest extends AbstractServiceTest
{
    public function testTrue()
    {
        $service = $this->createService();
        $service->attendance(1);
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
                DataHelper::newGroup(3),
                DataHelper::newGroup(4),
                DataHelper::newGroup(5),
            ],
            'User' => [
                ['id'=>1, 'name'=>'', 'passwd'=>'', 'email'=>'one@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>1],
                ['id'=>2, 'name'=>'', 'passwd'=>'', 'email'=>'two@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>3, 'name'=>'', 'passwd'=>'', 'email'=>'three@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],

                ['id'=>4, 'name'=>'', 'passwd'=>'', 'email'=>'four@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>5, 'name'=>'', 'passwd'=>'', 'email'=>'five@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>6, 'name'=>'', 'passwd'=>'', 'email'=>'six@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>7, 'name'=>'', 'passwd'=>'', 'email'=>'seven@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>8, 'name'=>'', 'passwd'=>'', 'email'=>'eight@mail.com', 'title'=>'', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
            ],
            'Group_has_User' => [
                [ 'group_id'=>1, 'user_id'=>1, 'type'=>2 ],
                [ 'group_id'=>2, 'user_id'=>1, 'type'=>1 ],
                [ 'group_id'=>2, 'user_id'=>2, 'type'=>0 ],
                [ 'group_id'=>2, 'user_id'=>3, 'type'=>0 ],

                [ 'group_id'=>5, 'user_id'=>1, 'type'=>2 ],
                [ 'group_id'=>5, 'user_id'=>2, 'type'=>2 ],
                [ 'group_id'=>5, 'user_id'=>3, 'type'=>1 ],
                [ 'group_id'=>5, 'user_id'=>4, 'type'=>1 ],
                [ 'group_id'=>5, 'user_id'=>5, 'type'=>1 ],
                [ 'group_id'=>5, 'user_id'=>6, 'type'=>0 ],
                [ 'group_id'=>5, 'user_id'=>7, 'type'=>0 ],
            ],
        ]);
    }

    protected function getServiceClass()
    {
        return User::class;
    }
}
