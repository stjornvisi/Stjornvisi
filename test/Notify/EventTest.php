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
                'body' => 'myBody',
                'subject' => 'mySubject',
                'event_id' => 1,
            ]
        ]);

        $this->assertInstanceOf(Event::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
        $this->checkPublishedNames(['n1']);
        $this->checkChannelBody('myBody');
        $this->checkChannelBody('href="/vidburdir/1"');
        $this->checkChannelSubject('mySubject');
    }

    public function testToAllWithGroupsOk()
    {
        $notifier = new Event();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
                'recipients' => 'allir',
                'test' => false,
                'body' => 'myBody',
                'subject' => 'mySubject',
                'event_id' => 3,
            ]
        ]);

        $this->assertInstanceOf(Event::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
        $this->checkPublishedNames(['n3']);
        $this->checkChannelBody('myBody');
        $this->checkChannelBody('href="/vidburdir/3"');
        $this->checkChannelSubject('mySubject');
    }

    public function testToAllWithoutGroupsOk()
    {
        $notifier = new Event();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
                'recipients' => 'allir',
                'test' => false,
                'body' => 'myBody',
                'subject' => 'mySubject',
                'event_id' => 4,
            ]
        ]);

        $this->assertInstanceOf(Event::class, $notifier->send());
        $this->checkNumChannelPublishes(3);
        $this->checkPublishedNames(['n1', 'n2', 'n3']);
        $this->checkChannelBody('myBody', 2);
        $this->checkChannelBody('href="/vidburdir/4"', 2);
        $this->checkChannelSubject('mySubject', 2);
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
     * Send only to sender when running Staging environment
     */
    public function testStagingSendOnlyToSender()
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

        putenv('APPLICATION_ENV=staging');
        $this->assertInstanceOf(Event::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
        $this->checkPublishedNames(['n1']);
        putenv('APPLICATION_ENV=testing');
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'User' => [
                DataHelper::newUser(1, 0),
                DataHelper::newUser(2, 0),
                DataHelper::newUser(3, 0),
            ],
            'Event' => [
                DataHelper::newEvent(1, null, ['subject' => 's1']),
                DataHelper::newEvent(2, null, ['subject' => 's1']),
                DataHelper::newEvent(3, null, ['subject' => 's3']),
                DataHelper::newEvent(4, null, ['subject' => 's4']),
            ],
            'Group' => [
                DataHelper::newGroup(1),
                DataHelper::newGroup(2),
            ],
            'Group_has_Event' => [
                DataHelper::newGroupHasEvent(1, 1),
                DataHelper::newGroupHasEvent(1, null),
                DataHelper::newGroupHasEvent(3, 2),
            ],
            'Event_has_Guest' => [],
            'Company' => [],
            'Company_has_User' => [],
            'Group_has_User' => [
                DataHelper::newGroupHasUser(2, 3),
            ],
            'Event_has_User' => [
                DataHelper::newEventHasUser(1, 1, 1),
                DataHelper::newEventHasUser(3, 3, 1),
            ],
        ]);
    }

    /**
     * @return string
     */
    protected function getNotifierClass()
    {
        return Event::class;
    }
}
