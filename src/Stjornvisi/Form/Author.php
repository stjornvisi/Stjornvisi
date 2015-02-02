<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 10/03/14
 * Time: 20:48
 */

namespace Stjornvisi\Form;


use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Author extends Form implements InputFilterProviderInterface{

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
			),
			'options' => array(
				'label' => 'Texti',
			),
		));

		$this->add(array(
			'name' => 'avatar',
			'type' => 'Stjornvisi\Form\Element\Img',
			'attributes' => array(
				'placeholder' => 'Mynd...',
				'data-url' => '/skrar/mynd',	//TODO can I use a function to call the router?
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


	/**
	 * Should return an array specification compatible with
	 * {@link Zend\InputFilter\Factory::createInputFilter()}.
	 *
	 * @return array
	 */
	public function getInputFilterSpecification(){
		return array(
			'name' => array(
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name'    => 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'      => 1,
							'max'      => 100,
						),
					),
				),
			),
			'info' => array(
				'required' => false,
				'allow_empty' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
			'avatar' => array(
				'required' => false,
				'allow_empty' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),

		);
	}
}
