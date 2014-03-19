<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 10/03/14
 * Time: 20:48
 */

namespace Stjornvisi\Form;


use Zend\Form\Form;

class Author extends Form {

	public function __construct(){

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
				'label' => 'Titill',
			),
		));

		$this->add(array(
			'name' => 'info',
			'type' => 'Zend\Form\Element\Textarea',
			'attributes' => array(
				'placeholder' => 'Texti...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Texti',
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
