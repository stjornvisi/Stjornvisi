<?php

namespace Stjornvisi\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterInterface;

class EventDatepicker extends Form
{
    public function __construct(array $authors = array())
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'annualdate',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'dd/mm',
                'class' => 'form-control'
            ),
            'options' => array(
                'label' => 'Árleg dagsetning'
            ),
        ));

        $this->add(array(
            'name' => 'specificdate',
            'type' => 'Stjornvisi\Form\Element\Datepicker',
            'attributes' => array(
                'placeholder' => 'dd/mm/yyyy',
                'class' => 'form-control datepicker'
            ),
            'options' => array(
                'label' => 'Sérstök dagsetning',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Submit',
                'class' => 'btn btn-default'
            ),
            'options' => array(
                'label' => 'Submit',
            ),
        ));
    }
}
