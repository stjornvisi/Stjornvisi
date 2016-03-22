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
    public function testOk()
    {
        $notifier = new Submission();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'recipient' => 1,
                'group_id' => 1,
                'register' => true
            ]
        ]);

        $this->assertInstanceOf('\Stjornvisi\Notify\Submission', $notifier->send());
    }

    /**
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testConnectionException()
    {
        $notifier = new Submission();
        $this->prepareNotifier($notifier, true);
        $notifier->setData((object)[
            'data' => (object)[
                'recipient' => 1,
                'group_id' => 1,
                'register' => true
            ]
        ]);

        $this->assertInstanceOf('\Stjornvisi\Notify\Submission', $notifier->send());
    }

    /**
     *
     * @expectedExceptionMessage User [100] not found
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testUserNotFound()
    {
        $notifier = new Submission();
        $this->prepareNotifier($notifier, true);
        $notifier->setData((object)[
            'data' => (object)[
                'recipient' => 100,
                'group_id' => 1,
                'register' => true
            ]
        ]);

        $this->assertInstanceOf('\Stjornvisi\Notify\Submission', $notifier->send());
    }

    /**
     *
     * @expectedExceptionMessage Group [100] not found
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testGroupNotFound()
    {
        $notifier = new Submission();
        $this->prepareNotifier($notifier, true);
        $notifier->setData((object)[
            'data' => (object)[
                'recipient' => 1,
                'group_id' => 100,
                'register' => true
            ]
        ]);

        $this->assertInstanceOf('\Stjornvisi\Notify\Submission', $notifier->send());
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet(DataHelper::getEventsDataSet());
    }
}
