<?php

namespace Stjornvisi\Form;

use Stjornvisi\ArrayDataSet;
use Stjornvisi\Bootstrap;
use Stjornvisi\DatabaseTestCase;
use Stjornvisi\DataHelper;
use Stjornvisi\Service\Values;
use Zend\Form\Element\Select as SelectElement;

class NewUserCompanySelectTest extends DatabaseTestCase
{
    public function testCreate()
    {
        $form = $this->createForm();
        $this->assertInstanceOf(NewUserCompanySelect::class, $form);
        /** @var SelectElement $element */
        $element = $form->get('company-select');
        $this->assertInstanceOf(SelectElement::class, $element);
        $options = $element->getValueOptions();
        $this->assertCount(4, $options); // No University or Person types
        $this->assertEquals(['n1','n2','n3','n4',], array_values($options));
    }

    /**
     * @param $email
     * @param $expectedCompanyId
     * @dataProvider provideDetect
     */
    public function testDetect($email, $expectedCompanyId)
    {
        $form = $this->createForm();
        $form->detectFromEmail($email);
        /** @var SelectElement $element */
        $element = $form->get('company-select');
        $this->assertEquals($expectedCompanyId, $element->getValue());
    }

    public function testMissingEmailDoesNotCrash()
    {
        $form = $this->createForm();
        $form->detectFromEmail(null);
        $form->detectFromEmail('');
    }

    public function provideDetect()
    {
        return [
            'NonExisting' => ['test@stefna.is', null],
            'Works80' => ['test@test1.com', 1],
            'Works443' => ['test@test2.com', 2],
            'WorksNoSchema' => ['test@test3.com', 3],
        ];
    }

    /**
     * @return NewUserCompanySelect
     */
    private function createForm()
    {
        return Bootstrap::getServiceManager()->get(NewUserCompanySelect::class);
    }

    /**
     * Returns the test dataset.
     *
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new ArrayDataSet([
            'Company' => [
                DataHelper::newCompany(1, 'hf', ['website' => 'http://www.test1.com']),
                DataHelper::newCompany(2, 'sf', ['website' => 'https://www.test2.com']),
                DataHelper::newCompany(3, 'ohf', ['website' => 'www.test3.com']),
                DataHelper::newCompany(4, 'hf', ['website' => null]),
                DataHelper::newCompany(5, Values::COMPANY_TYPE_UNIVERSITY),
                DataHelper::newCompany(6, Values::COMPANY_TYPE_PERSON),
            ],
        ]);
    }
}
