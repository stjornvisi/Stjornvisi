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
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
                'facebook' => 'akdjfghseiurg'
            ]
        ]);

        $this->assertInstanceOf(UserValidate::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
        $this->checkPublishedNames(['n1']);
        $this->checkChannelBody('smella á hlekkinn hér fyrir neðan');
        $this->checkChannelSubject('Stjórnvísi, staðfesting á aðgangi');
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
                'user_id' => 1,
                'facebook' => 'akdjfghseiurg'
            ]
        ]);

        $this->assertInstanceOf(UserValidate::class, $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage Missing data:facebook
     */
    public function testMissingFacebook()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
            ]
        ]);

        $this->assertInstanceOf(UserValidate::class, $notifier->send());
    }

    /**
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage User [100] not found
     */
    public function testUserNotFound()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 100,
                'facebook' => 'akdjfghseiurg'
            ]
        ]);

        $this->assertInstanceOf(UserValidate::class, $notifier->send());
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet(DataHelper::getEventsDataSet());
    }

    /**
     * @return string
     */
    protected function getNotifierClass()
    {
        return UserValidate::class;
    }
}
