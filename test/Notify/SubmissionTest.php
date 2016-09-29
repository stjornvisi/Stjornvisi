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

class SubmissionTest extends AbstractTestCase
{
    public function testRegisterOk()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'recipient' => 1,
                'group_id' => 1,
                'register' => true
            ]
        ]);

        $this->assertInstanceOf(Submission::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
        $this->checkPublishedNames(['n1']);
        $this->checkChannelBody('Þú hefur ákveðið að skrá þig í hópinn <strong>n1');
        $this->checkChannelSubject('Þú hefur skráð þig í hópinn: n1');
    }

    public function testUnRegisterOk()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'recipient' => 1,
                'group_id' => 1,
                'register' => false
            ]
        ]);

        $this->assertInstanceOf(Submission::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
        $this->checkPublishedNames(['n1']);
        $this->checkChannelBody('Þú hefur ákveðið að afskrá þig úr hópnum <strong>n1');
        $this->checkChannelSubject('Þú hefur afskráð þig úr hópnum: n1');
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
                'recipient' => 1,
                'group_id' => 1,
                'register' => true
            ]
        ]);

        $this->assertInstanceOf(Submission::class, $notifier->send());
    }

    /**
     *
     * @expectedExceptionMessage User [100] not found
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testUserNotFound()
    {
        $notifier = $this->createNotifier(true);
        $notifier->setData((object)[
            'data' => (object)[
                'recipient' => 100,
                'group_id' => 1,
                'register' => true
            ]
        ]);

        $this->assertInstanceOf(Submission::class, $notifier->send());
    }

    /**
     *
     * @expectedExceptionMessage Group [100] not found
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testGroupNotFound()
    {
        $notifier = $this->createNotifier(true);
        $notifier->setData((object)[
            'data' => (object)[
                'recipient' => 1,
                'group_id' => 100,
                'register' => true
            ]
        ]);

        $this->assertInstanceOf(Submission::class, $notifier->send());
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
        return Submission::class;
    }
}
