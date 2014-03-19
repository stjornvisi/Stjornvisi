<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/13/14
 * Time: 11:06 PM
 */

namespace Stjornvisi\Form;


use Zend\Form\Element;
use Zend\Form\Form;

class  Company extends Form{

    public function __construct($type, $code, $size){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

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
            'name' => 'ssn',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Kennitala...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Kennitala',
            ),
        ));

        $this->add(array(
            'name' => 'address',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Heimilisfang...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Heimilisfang',
            ),
        ));

        $this->add(array(
            'name' => 'zip',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Póstfang',
                'value_options' => $code,
            ),
        ));

        $this->add(array(
            'name' => 'business_type',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Rekstrarform',
                'value_options' => $type,
            ),
        ));

        $this->add(array(
            'name' => 'number_of_employees',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Starfsmannafjöldi',
                'value_options' => $size,
            ),
        ));

        $this->add(array(
            'name' => 'website',
            'type' => 'Zend\Form\Element\Url',
            'attributes' => array(
                'placeholder' => 'Heimasíða...',
            ),
            'options' => array(
                'label' => 'Heimasíða',
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
