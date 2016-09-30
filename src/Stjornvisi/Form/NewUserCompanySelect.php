<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 07/11/14
 * Time: 10:00
 */

namespace Stjornvisi\Form;

use Stjornvisi\Service\Company;

use Stjornvisi\Service\Values;
use Zend\Form\Form;

class NewUserCompanySelect extends Form
{
    public function __construct(Company $company)
    {
        $companies = $company->fetchAll([
            Values::COMPANY_TYPE_PERSON,
            Values::COMPANY_TYPE_UNIVERSITY,
        ]);
        $options = array();
        foreach ($companies as $item) {
            $options[$item->id] = $item->name;
        }

        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'company-select',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'placeholder' => 'Nafn...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Fyrirtæki',
                'empty_option' => 'Veldu fyrirtæki',
                'value_options' => $options
            ),
        ));

        $this->add(array(
            'name' => 'submit-company-select',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Submit',
            ),
            'options' => array(
                'label' => 'Submit',
            ),
        ));

    }
}
