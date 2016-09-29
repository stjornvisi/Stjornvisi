<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 16/02/15
 * Time: 12:21
 */

namespace Stjornvisi\Notify;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\Bootstrap;
use Stjornvisi\DataHelper;

require_once 'AbstractTestCase.php';

class DigestTest extends AbstractTestCase
{

    public function testOk()
    {
        $notifier = $this->createNotifier();

        $this->assertInstanceOf(Digest::class, $notifier->send());
        $this->checkNumChannelPublishes(2);
        $this->checkChannelBody('Vikan <strong>');
        $this->checkChannelBody('href="/vidburdir/9"'); // 9 is the last event
        $this->checkChannelSubject('Vikan framundan', 1);
        $this->checkPublishedNames(['n1', 'n2']);
    }

    public function testNoEvents()
    {
        $date = DataHelper::createDate((new \DateTime())->setDate(2016, 1, 1));
        $sql = "UPDATE Event SET event_date = '$date'";
        Bootstrap::getConnection()->exec($sql);
        $notifier = $this->createNotifier();
        $this->assertInstanceOf(Digest::class, $notifier->send());
        $this->checkNumChannelPublishes(0);
    }

    /**
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testConnectionException()
    {
        $notifier = $this->createNotifier(true);

        $this->assertInstanceOf(Digest::class, $notifier->send());
    }

    /**
     * Send only to sender when running Staging environment
     */
    public function testStagingSendOnlyToSender()
    {
        $notifier = $this->createNotifier();

        putenv('APPLICATION_ENV=staging');
        Bootstrap::authenticateUser(1);

        $this->assertInstanceOf(Digest::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
        $this->checkPublishedNames(['n1']);
        putenv('APPLICATION_ENV=testing');
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
        return Digest::class;
    }
}
