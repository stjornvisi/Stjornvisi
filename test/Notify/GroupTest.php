<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 16/02/15
 * Time: 12:21
 */

namespace Stjornvisi\Notify;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\DataHelper;

require_once 'AbstractTestCase.php';

class GroupTest extends AbstractTestCase
{
    public function testOkTest()
    {
        $notifier = new Group();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'recipients' => 'allir',
                'test' => true,
                'sender_id' => 1,
                'group_id' => 1,
                'body' => 'nothing',
                'subject' => '',
            ]
        ]);

        $this->assertInstanceOf(Group::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
    }

    public function testOk()
    {
        $notifier = new Group();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'recipients' => 'allir',
                'test' => false,
                'sender_id' => 1,
                'group_id' => 1,
                'body' => 'nothing',
                'subject' => '',
            ]
        ]);

        $this->assertInstanceOf(Group::class, $notifier->send());
        $this->checkNumChannelPublishes(2);
    }

    /**
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testConnectionException()
    {
        $notifier = new Group();
        $this->prepareNotifier($notifier, true);
        $notifier->setData((object)[
            'data' => (object)[
                'recipients' => 'allir',
                'test' => true,
                'sender_id' => 1,
                'group_id' => 1,
                'body' => 'nothing',
                'subject' => '',
            ]
        ]);

        $this->assertInstanceOf(Group::class, $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage Group [100] not found
     */
    public function testGroupNotFound()
    {
        $notifier = new Group();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'recipients' => 'allir',
                'test' => true,
                'sender_id' => 1,
                'group_id' => 100,
                'body' => 'nothing',
                'subject' => '',
            ]
        ]);

        $this->assertInstanceOf(Group::class, $notifier->send());
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet(DataHelper::getEventsDataSet());
    }
}
