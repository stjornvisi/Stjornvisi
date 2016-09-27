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
        $notifier = new Digest();
        $this->prepareNotifier($notifier);

        $this->assertInstanceOf(Digest::class, $notifier->send());
        $this->checkNumChannelPublishes(2);
    }

    /**
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testConnectionException()
    {
        $notifier = new Digest();
        $this->prepareNotifier($notifier, true);

        $this->assertInstanceOf(Digest::class, $notifier->send());
    }

    /**
     * Send only to sender when running Staging environment
     */
    public function testStagingSendOnlyToSender()
    {
        $notifier = new Digest();
        $this->prepareNotifier($notifier);

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
}
