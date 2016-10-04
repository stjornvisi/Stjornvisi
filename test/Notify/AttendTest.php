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
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 1,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notifier->send();
        $this->checkNumChannelPublishes(1);
        $this->checkChannelBody('<p>Skráning á viðburð staðfest:</p>');
        $this->checkChannelSubject('Þú hefur skráð þig á viðburðinn: s1');
    }
    public function testEverythingOkWithPresenter()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'event_id' => 3,
                'recipients' => 1,
                'type' => true
            ]
        ]);
        $notifier->send();
        $this->checkNumChannelPublishes(1);
        $this->checkChannelBody('<p>Skráning á viðburð staðfest:</p>');
        $this->checkChannelBody('<span>Presenter001</span>');
        $this->checkChannelBody('<img src="/stjornvisi/images/medium/1x@presenter001.jpg"');
        $this->checkChannelSubject('Þú hefur skráð þig á viðburðinn: s3');
    }

    public function testEverythingWithGuestUser()
    {
        $notifier = $this->createNotifier();
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
        $notifier = $this->createNotifier();
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
        $notifier = $this->createNotifier();
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
        $notifier = $this->createNotifier();
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
        $notifier = $this->createNotifier(true);
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
                DataHelper::newEvent(3, null, [
                	'presenter1' => 'Presenter001',
					'presenter1_avatar' => 'presenter001.jpg',
				]),
            ],
        ]);
    }

    /**
     * @return string
     */
    protected function getNotifierClass()
    {
        return Attend::class;
    }
}
