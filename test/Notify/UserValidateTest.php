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

class UserValidateTest extends AbstractTestCase
{
    public function testOk()
    {
        $notifier = new UserValidate();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
                'facebook' => 'akdjfghseiurg'
            ]
        ]);

        $this->assertInstanceOf('\Stjornvisi\Notify\UserValidate', $notifier->send());
    }

    /**
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testConnectionException()
    {
        $notifier = new UserValidate();
        $this->prepareNotifier($notifier, true);
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
                'facebook' => 'akdjfghseiurg'
            ]
        ]);

        $this->assertInstanceOf('\Stjornvisi\Notify\UserValidate', $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage User [100] not found
     */
    public function testUserNotFound()
    {
        $notifier = new UserValidate();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => true,
                'recipient' => 1,
                'body' => 'nothing',
                'subject' => '',
                'group_id' => 1,
                'user_id' => 100,
                'facebook' => 'akdjfghseiurg'
            ]
        ]);

        $this->assertInstanceOf('\Stjornvisi\Notify\UserValidate', $notifier->send());
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet(DataHelper::getEventsDataSet());
    }
}
