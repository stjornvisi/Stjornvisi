<?php

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class User extends Form
{
    public function __construct($companies = array(), $titles = array())
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Nafn...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Nafn',
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'placeholder' => 'Netfang...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Netfang',
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Titill',
                'value_options' => $titles,
            ),
        ));

        $companyArray = array();
        foreach ($companies as $company) {
            $companyArray[$company->id] = $company->name;
        }
        $this->add(array(
            'name' => 'company',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Fyrirtæki',
                'value_options' => $companyArray,
            ),
        ));

        $this->add(array(
            'name' => 'submit',
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
