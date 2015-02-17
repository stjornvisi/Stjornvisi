<?php
namespace Stjornvisi\Form;

use Zend\Form\Form;
/**
 * Log in user
 * 
 * @category Stjornvisi
 * @package Form
 * @author einar
 * @deprecated
 */
class Login extends Form{

    public function __construct($name = null){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

		$this->setAttribute('method', 'post')->setAttribute('action','/innskra');

        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Netfang',
            ),
        ));
        $this->add(array(
            'name' => 'passwd',
            'attributes' => array(
                'type'  => 'password',
            ),
            'options' => array(
                'label' => 'Lykilorð',
            ),
        ));
		/*
        $this->add(array(
            'name' => 'rememberme',
            'type' => 'checkbox', // 'Zend\Form\Element\Checkbox',
//            'attributes' => array( // Is not working this way
//                'type'  => '\Zend\Form\Element\Checkbox',
//            ),
            'options' => array(
                'label' => 'Remember Me?',
//                                'checked_value' => 'true', without value here will be 1
//                                'unchecked_value' => 'false', // witll be 1
            ),
        ));
		*/
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Innskrá',
                'id' => 'submitbutton',
            ),
        ));
    }


}

