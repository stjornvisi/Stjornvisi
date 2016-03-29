<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 29/09/14
 * Time: 11:53
 */

namespace Stjornvisi\Notify;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\DataHelper;

require_once 'AbstractTestCase.php';

class AttendTest extends AbstractTestCase
{
    public function testEverythingOk()
    {
        $notifier = new Attend();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notifier->send();
        $this->checkNumChannelPublishes(1);
    }

    public function testEverythingWithGuestUser()
    {
        $notifier = new Attend();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => (object)['name' => 'n1', 'email' => 'e@e.is'],
                'type' => true
            ]
        ]);
        $notifier->send();
        $this->checkNumChannelPublishes(1);
    }

    /**
     * @expectedExceptionMessage No recipient provided
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testNoUserProvided()
    {
        $notifier = new Attend();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => null,
                'type' => true
            ]
        ]);
        $notifier->send();
    }

    /**
     * @expectedExceptionMessage User [100] not found
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testUserNotFound()
    {
        $notifier = new Attend();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => 100,
                'type' => true
            ]
        ]);
        $notifier->send();
    }

    /**
     * @expectedExceptionMessage Event [100] not found
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testEventNotFound()
    {
        $notifier = new Attend();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 100,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notifier->send();
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testConnectionException()
    {
        $notifier = new Attend();
        $this->prepareNotifier($notifier, true);
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notifier->send();
    }

    /**
     * @expectedException \PDOException
     */
    public function testEverythingNotOk()
    {
        $notifier = new Attend();
        $this->prepareNotifier($notifier);
        $notifier->setDateStore(array_merge($this->getDatabaseConnectionValues(), ['user' => 'hvadagaurertetta']));
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notifier->send();
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'User' => [
                DataHelper::newUser(1, 0, ['passwd' => '1234']),
                DataHelper::newUser(2, 0, ['passwd' => '1234']),
            ],
            'Event' => [
                DataHelper::newEvent(1),
                DataHelper::newEvent(2),
            ],
        ]);
    }
}
