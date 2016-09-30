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

class NewUserUniversitySelect extends Form
{
    public function __construct(Company $company)
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $companies = $company->fetchType([Values::COMPANY_TYPE_UNIVERSITY]);
        $options = array();
        foreach ($companies as $item) {
            $options[$item->id] = $item->name;
        }

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'university-select',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'placeholder' => 'Háskólar',
            ),
            'options' => array(
                'label' => 'Háskólar',
                'empty_option' => 'Veldu háskóla',
                'value_options' => $options
            ),
        ));

        $this->add(array(
            'name' => 'submit-university-select',
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
