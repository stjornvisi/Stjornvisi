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

class PasswordTest extends AbstractTestCase
{
    public function testOk()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'recipients' => (object)['name' => 'hundur', 'email' => 'hundur@hundur.com'],
                'password' => '1234567890'
            ]
        ]);

        $this->assertInstanceOf(Password::class, $notifier->send());
    }

    /**
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testConnectionException()
    {
        $notifier = $this->createNotifier(true);
        $notifier->setData((object)[
            'data' => (object)[
                'recipients' => (object)['name' => 'hundur', 'email' => 'hundur@hundur.com'],
                'password' => '1234567890'
            ]
        ]);

        $this->assertInstanceOf(Password::class, $notifier->send());
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'User' => [
                DataHelper::newUser(1),
                DataHelper::newUser(2),
            ],
            'Group' => [
                DataHelper::newGroup(1),
                DataHelper::newGroup(2),
                DataHelper::newGroup(3),
                DataHelper::newGroup(4),
            ],
            'Event' => DataHelper::newEventSeries(),
            'Group_has_Event' => [
                DataHelper::newGroupHasEvent(2, 1, 0),
                DataHelper::newGroupHasEvent(2, 2, 0),
                DataHelper::newGroupHasEvent(2, 3, 0),

                DataHelper::newGroupHasEvent(3, 2, 0),
                DataHelper::newGroupHasEvent(4, null, 0),
            ],
            'Event_has_Guest' => [
                DataHelper::newEventHasGuest(1, 'n1', 'e@a.is'),
                DataHelper::newEventHasGuest(1, 'n2', 'b@a.is'),
                DataHelper::newEventHasGuest(9, 'n1', 'e@a.is'),
                DataHelper::newEventHasGuest(9, 'n2', 'b@a.is'),
            ],
            'Event_has_User' => [
                DataHelper::newEventHasUser(1, 1, 1),
                DataHelper::newEventHasUser(9, 1, 1),
                DataHelper::newEventHasUser(2, 2, 1),
            ],
            'EventMedia' => DataHelper::newEventMediaSeries(),
        ]);
    }

    /**
     * @return string
     */
    protected function getNotifierClass()
    {
        return Password::class;
    }
}
