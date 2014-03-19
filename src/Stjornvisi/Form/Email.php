<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/2/14
 * Time: 1:23 PM
 */

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class  Email extends Form{
    public function __construct($name = null){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'subject',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Efni',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Efni',
            ),
        ));

        $this->add(array(
            'name' => 'body',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'placeholder' => 'Texti',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Texti',
            ),
        ));

        $this->add(array(
            'name' => 'send',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Senda',
            ),
            'options' => array(
                'label' => 'Senda'
            ),
        ));

        $this->add(array(
            'name' => 'test',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Prufa',
            ),
            'options' => array(
                'label' => 'Prufa'
            ),
        ));

    }
} 
