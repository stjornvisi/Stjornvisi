<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/9/14
 * Time: 3:10 PM
 */

namespace Stjornvisi\Service;

use Stjornvisi\DataHelper;
use Stjornvisi\ArrayDataSet;

require_once 'AbstractServiceTest.php';
class EventTest extends AbstractServiceTest
{

    public function testGet()
    {
        $service = $this->createService();

        $this->assertInstanceOf('stdClass', $service->get(1));
        $this->assertInstanceOf('stdClass', $service->get(2));

        $this->assertInstanceOf('stdClass', $service->get(1, 1));

        $this->assertFalse($service->get(1000));
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'User' => [
                ['id'=>1, 'name'=>'n1', 'passwd'=>md5(rand(0, 9)), 'email'=>'e@mail.com', 'title'=>'t1', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
                ['id'=>2, 'name'=>'n1', 'passwd'=>md5(rand(0, 9)), 'email'=>'e@mail2.com', 'title'=>'t1', 'created_date'=>date('Y-m-d H:i:s'), 'modified_date'=>date('Y-m-d H:i:s'), 'frequency'=>1, 'is_admin'=>0],
            ],
            'Group' => [
                DataHelper::newGroup(1),
                DataHelper::newGroup(2),
                DataHelper::newGroup(3),
                DataHelper::newGroup(4),
            ],
            'Event' => [
                ['id'=>1, 'subject'=>'01', 'body'=>'01', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-4 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>2, 'subject'=>'02', 'body'=>'02', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-3 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>3, 'subject'=>'03', 'body'=>'03', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-2 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>4, 'subject'=>'04', 'body'=>'04', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-1 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>5, 'subject'=>'05', 'body'=>'05', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d'),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>6, 'subject'=>'06', 'body'=>'06', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('+1 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>7, 'subject'=>'07', 'body'=>'07', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('+2 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>8, 'subject'=>'08', 'body'=>'08', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('+3 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
                ['id'=>9, 'subject'=>'09', 'body'=>'09', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('+4 days')),'event_time'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null],
            ],
            'Group_has_Event' => [
                ['event_id'=>2, 'group_id'=>1,'primary'=>0],
                ['event_id'=>2, 'group_id'=>2,'primary'=>0],
                ['event_id'=>2, 'group_id'=>3,'primary'=>0],

                ['event_id'=>3, 'group_id'=>2,'primary'=>0],
                ['event_id'=>4, 'group_id'=>null,'primary'=>0],
            ],
            'Event_has_Guest' => [
                ['event_id'=>1,'name'=>'n1','email'=>'e@a.is','register_time'=>date('Y-m-d H:i:s')],
                ['event_id'=>1,'name'=>'n2','email'=>'b@a.is','register_time'=>date('Y-m-d H:i:s')],
                ['event_id'=>9,'name'=>'n1','email'=>'e@a.is','register_time'=>date('Y-m-d H:i:s')],
                ['event_id'=>9,'name'=>'n2','email'=>'b@a.is','register_time'=>date('Y-m-d H:i:s')],
            ],
            'Event_has_User' => [
                ['event_id' => 1, 'user_id'=>1,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],
                ['event_id' => 9, 'user_id'=>1,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],

                ['event_id' => 2, 'user_id'=>2,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],
            ],
            'EventMedia' => [
                ['id' => 1, 'name' => 'hundur1', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 2, 'name' => 'hundur2', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 3, 'name' => 'hundur3', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 4, 'name' => 'hundur4', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 5, 'name' => 'hundur5', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 6, 'name' => 'hundur6', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 7, 'name' => 'hundur7', 'event_id' => 4, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 8, 'name' => 'hundur8', 'event_id' => 4, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
            ],

        ]);
    }

    protected function getServiceClass()
    {
        return Event::class;
    }
}
