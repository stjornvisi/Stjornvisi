<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 8/04/15
 * Time: 6:28 AM
 */

namespace Stjornvisi\Notify;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\DataHelper;

require_once 'AbstractTestCase.php';

class AllTest extends AbstractTestCase
{
    /**
     * Everything should run.
     *
     * @throws NotifyException
     */
    public function testEverythingWorks()
    {
        $notifier = new All();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => true,
                'recipients' => 'allir',
                'body' => 'nothing',
                'subject' => ''
            ]
        ]);

        $this->assertInstanceOf(All::class, $notifier->send());
        $this->checkNumChannelPublishes(1); // 1, because we are only testing (test=true)
    }

    /**
     * Everything should run.
     *
     * @throws NotifyException
     */
    public function testGetAllUsersNotTest()
    {
        $notifier = new All();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => false,
                'recipients' => 'allir',
                'body' => 'nothing',
                'subject' => ''
            ]
        ]);

        $this->assertInstanceOf(All::class, $notifier->send());
        $this->checkNumChannelPublishes(3);
    }

    /**
     * @throws NotifyException
     * @expectedException \PDOException
     */
    public function testNotProvidingRightCredentialsForDatabase()
    {
        $notifier = new All();
        $this->prepareNotifier($notifier);
        $notifier->setDateStore(
            array_merge($this->getDatabaseConnectionValues(), ['user' => 'tettaerskoekkinotandi'])
        );
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => true,
                'recipients' => 'allir',
                'subject' => '',
                'body' => ''
            ]
        ]);

        $this->assertInstanceOf(All::class, $notifier->send());
    }

    /**
     * Missing properties in the data object provided
     * to an instance of the Notifier.
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     * @expectedExceptionMessage Missing data:subject
     */
    public function testNotPassingIntRequiredDataPropertiesException()
    {
        $notifier = new All();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => true,
                'recipients' => 'allir'
            ]
        ]);

        $this->assertInstanceOf(All::class, $notifier->send());

    }

    /**
     * RabbitMQ can't connect to server.
     *
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testQueueThrowingConnectionException()
    {
        $notifier = new All();
        $this->prepareNotifier($notifier, true);
        $notifier->setData((object)[
            'data' => (object)[
                'subject' => '',
                'sender_id' => 1,
                'test' => true,
                'body' => '',
                'recipients' => 'allir'
            ]
        ]);

        $this->assertInstanceOf(All::class, $notifier->send());

    }

    /**
     * Send only to sender when running Staging environment
     */
    public function testStagingSendOnlyToSender()
    {
        $notifier = new All();
        $this->prepareNotifier($notifier);
        $notifier->setData((object)[
            'data' => (object)[
                'sender_id' => 1,
                'test' => false,
                'recipients' => 'allir',
                'body' => 'nothing',
                'subject' => ''
            ]
        ]);

        putenv('APPLICATION_ENV=staging');
        $this->assertInstanceOf(All::class, $notifier->send());
        $this->checkNumChannelPublishes(1);
        $this->checkPublishedNames(['n1']);
        putenv('APPLICATION_ENV=testing');
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new ArrayDataSet([
            'Company' => [
                DataHelper::newCompany(1, 'hf'),
                DataHelper::newCompany(2, 'sf'),
                DataHelper::newCompany(3, 'ohf'),
                DataHelper::newCompany(4, 'hf'),
            ],
            'User' => [
                DataHelper::newUser(1, 1),
                DataHelper::newUser(2, 0),
                DataHelper::newUser(3, 0),
            ],
            'Company_has_User' => [
                DataHelper::newCompanyHasUser(1, 1, 0),
                DataHelper::newCompanyHasUser(2, 1, 0),
            ],
        ]);
    }
}
