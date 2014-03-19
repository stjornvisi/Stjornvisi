<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 11/03/14
 * Time: 09:21
 */

namespace Stjornvisi\Form;


use Zend\Form\Form;

class LostPassword extends Form {

	public function __construct(){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

		$this->setAttribute('method', 'post');

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
