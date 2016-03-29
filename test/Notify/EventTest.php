<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 30/09/14
 * Time: 19:26
 */

namespace Stjornvisi\Notify;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\DataHelper;

require_once 'AbstractTestCase.php';

class EventTest extends AbstractTestCase
{
    public function testOk()
    {
        $notifier = new Event();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
                'recipients' => 'not allir',
                'test' => false,
                'body' => '',
                'subject' => '',
                'event_id' => 1,
            ]
        ]);

        $this->assertInstanceOf(Event::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage Event [100] not found
     */
    public function testEventNotFound()
    {
        $notifier = new Event();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 100,
                'user_id' => 1,
                'recipients' => 'allir',
                'test' => false,
                'body' => '',
                'subject' => ''
            ]
        ]);

        $this->assertInstanceOf(Event::class, $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage Sender not found
     */
    public function testUserNotFoundInTestMode()
    {
        $notifier = new Event();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'user_id' => 100,
                'recipients' => 'allir',
                'test' => true,
                'body' => '',
                'subject' => ''
            ]
        ]);

        $this->assertInstanceOf(Event::class, $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testQueueConnectionException()
    {
        $notifier = new Event();
        $this->prepareNotifier($notifier, true);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'user_id' => 1,
                'recipients' => 'allir',
                'test' => true,
                'body' => '',
                'subject' => ''
            ]
        ]);

        $this->assertInstanceOf(Event::class, $notifier->send());
    }

    /**
     *
     */
    public function testUserFoundInTestMode()
    {
        $notifier = new Event();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'user_id' => 1,
                'recipients' => 'not allir',
                'test' => true,
                'body' => '',
                'subject' => ''
            ]
        ]);

        $this->assertInstanceOf(Event::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage No users found for event notification
     */
    public function testEveryBodyNotOk()
    {
        $notifier = new Event();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'user_id' => 1,
                'recipients' => 'allir',
                'test' => false,
                'body' => '',
                'subject' => ''
            ]
        ]);

        $this->assertInstanceOf(Event::class, $notifier->send());
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'User' => [
                DataHelper::newUser(1, 0, ['email' => null]),
                DataHelper::newUser(2, 0, ['email' => null]),
            ],
            'Event' => [
                DataHelper::newEvent(1, null, ['subject' => 's1']),
                DataHelper::newEvent(2, null, ['subject' => 's1']),
            ],
            'Group' => [
                DataHelper::newGroup(1),
            ],
            'Group_has_Event' => [
                DataHelper::newGroupHasEvent(1, 1),
                DataHelper::newGroupHasEvent(1, null),
            ],
            'Event_has_Guest' => [],
            'Company' => [],
            'Company_has_User' => [],
        ]);
    }
}
