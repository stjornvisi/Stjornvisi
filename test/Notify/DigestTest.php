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

class DigestTest extends AbstractTestCase
{

    public function testOk()
    {
        $notifier = new Digest();
        $this->prepareNotifier($notifier);

        $this->assertInstanceOf(Digest::class, $notifier->send());
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
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet(DataHelper::getEventsDataSet());
    }
}
