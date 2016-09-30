<?php

namespace Stjornvisi\Notify;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\DataHelper;
use Stjornvisi\Service\Values;

require_once 'AbstractTestCase.php';

class WelcomeTest extends AbstractTestCase
{
    public function testOkNoCompany()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'user_id' => 1,
            ]
        ]);
        $notifier->send();
        $this->check(null, 'n1');
    }

    public function testOkWithCompanyNormal()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'user_id'            => 1,
                'created_company_id' => 1,
            ]
        ]);
        $notifier->send();
        $this->check('n1');
    }

    public function testOkWithCompanyUniversity()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'user_id'            => 2,
                'created_company_id' => 2,
            ]
        ]);
        $notifier->send();
        $this->check(null, 'n2');
    }

    public function testOkWithCompanyPerson()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'user_id'            => 3,
                'created_company_id' => 3,
            ]
        ]);
        $notifier->send();
        $this->check(null, 'n3');
    }

    /**
     * @expectedExceptionMessage Missing data:user_id
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testNoUserProvided()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
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
                'user_id' => 100,
            ]
        ]);
        $notifier->send();
    }

    /**
     * @expectedExceptionMessage Company [100] not found
     * @expectedException \Stjornvisi\Notify\NotifyException
     */
    public function testCompanyNotFound()
    {
        $notifier = $this->createNotifier();
        $notifier->setData((object)[
            'data' => (object)[
                'user_id'            => 1,
                'created_company_id' => 100,
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
                'user_id'            => 1,
                'created_company_id' => 1,
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
            'User'             => [
                DataHelper::newUser(1, 0, ['passwd' => '1234']),
                DataHelper::newUser(2, 0, ['passwd' => '1234']),
                DataHelper::newUser(3, 0, ['passwd' => '1234']),
            ],
            'Company'          => [
                DataHelper::newCompany(1),
                DataHelper::newCompany(2, Values::COMPANY_TYPE_PERSON),
                DataHelper::newCompany(3, Values::COMPANY_TYPE_UNIVERSITY),
            ],
            'Company_has_User' => [
                DataHelper::newCompanyHasUser(1, 1),
                DataHelper::newCompanyHasUser(2, 2),
                DataHelper::newCompanyHasUser(3, 3),
            ],
        ]);
    }

    /**
     * @return string
     */
    protected function getNotifierClass()
    {
        return Welcome::class;
    }

    private function check(
        $companyNameExists = null,
        $companyNameNotExists = null
    ) {
        $this->checkNumChannelPublishes(1);
        $this->checkChannelBody('<p>Við óskum þér til hamingju');
        $this->checkChannelSubject('Velkomin/nn í Stjórnvísi');
        if ($companyNameExists) {
            $this->checkChannelBody("fyrirtæki: $companyNameExists</p>");
        }
        if ($companyNameNotExists) {
            $this->checkChannelBody("fyrirtæki: $companyNameNotExists</p>", 0,
                false);
        }
    }
}
