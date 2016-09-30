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
/**
 * Class EventGetTest
 * @package Stjornvisi\Service
 */
class EventGetTest extends AbstractServiceTest
{
    public function testGetResourceNotFound()
    {
        $this->assertFalse($this->createService()->get(1000));
    }

    public function testGet()
    {
        $service = $this->createService();
        $event = $service->get(1);

        $this->assertInternalType('int', $event->id);
        $this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_time);
        $this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_end);
        $this->assertInstanceOf('\DateTime', $event->event_date);
        $this->assertNull($event->capacity);
        $this->assertNull($event->avatar);

        $event = $service->get(2);

        $this->assertInternalType('int', $event->id);
        $this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_time);
        $this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_end);
        $this->assertInstanceOf('\DateTime', $event->event_date);
        $this->assertNull($event->capacity);
        $this->assertNull($event->avatar);

        $event = $service->get(3);

        $this->assertInternalType('int', $event->id);
        $this->assertInstanceOf('\Stjornvisi\Lib\Time', $event->event_time);
        $this->assertNull($event->event_end);
        $this->assertInstanceOf('\DateTime', $event->event_date);
        $this->assertInternalType('int', $event->capacity);
        $this->assertNotNull($event->avatar);
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
                ['id'=>1, 'subject'=>'01', 'body'=>'01', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-4 days')),'event_time'=>date('H:m'),'event_end'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null,'capacity'=>0],
                ['id'=>2, 'subject'=>'01', 'body'=>'01', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-4 days')),'event_time'=>date('H:m'),'event_end'=>date('H:m'),'avatar'=>null,'lat'=>null,'lng'=>null,'capacity'=>null],
                ['id'=>3, 'subject'=>'01', 'body'=>'01', 'location'=>'01', 'address'=>'', 'event_date'=>date('Y-m-d', strtotime('-4 days')),'event_time'=>date('H:m'),'event_end'=>null,       'avatar'=>'df','lat'=>null,'lng'=>null,'capacity'=>1],

            ],
            'Group_has_Event' => [
                ['event_id'=>2, 'group_id'=>1,'primary'=>0],
                ['event_id'=>2, 'group_id'=>2,'primary'=>0],
                ['event_id'=>2, 'group_id'=>3,'primary'=>0],

                ['event_id'=>3, 'group_id'=>2,'primary'=>0],

            ],
            'Event_has_Guest' => [
                ['event_id'=>1,'name'=>'n1','email'=>'e@a.is','register_time'=>date('Y-m-d H:i:s')],
                ['event_id'=>1,'name'=>'n2','email'=>'b@a.is','register_time'=>date('Y-m-d H:i:s')],

            ],
            'Event_has_User' => [
                ['event_id' => 1, 'user_id'=>1,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],


                ['event_id' => 2, 'user_id'=>2,'attending'=>1,'register_time'=>date('Y-m-d H:i:s')],
            ],
            'EventMedia' => [
                ['id' => 1, 'name' => 'hundur1', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 2, 'name' => 'hundur2', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 3, 'name' => 'hundur3', 'event_id' => 2, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 4, 'name' => 'hundur4', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 5, 'name' => 'hundur5', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
                ['id' => 6, 'name' => 'hundur6', 'event_id' => 3, 'description' => '', 'created'=>date('Y-m-d H:i:s')],
            ],

        ]);
    }

    protected function getServiceClass()
    {
        return Event::class;
    }
}
