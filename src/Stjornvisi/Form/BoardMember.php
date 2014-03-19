<?php

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class BoardMember extends Form{

	public function __construct($name = null){

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
			'name' => 'email',
			'type' => 'Zend\Form\Element\Email',
			'attributes' => array(
				'placeholder' => 'Netfang...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Netfng',
			),
		));

		$this->add(array(
			'name' => 'company',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Fyrirtæki...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Fyrirtæki',
			),
		));

		$this->add(array(
			'name' => 'avatar',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Mynd...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Mynd',
			),
		));

		$this->add(array(
			'name' => 'info',
			'type' => 'Zend\Form\Element\Textarea',
			'attributes' => array(
				'placeholder' => 'Upplýsingar...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Upplýsingar',
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
