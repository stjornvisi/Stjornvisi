<?php

namespace Stjornvisi\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Class Article
 *
 * Form to create articles.
 *
 * @package Stjornvisi\Form
 */
class Article extends Form implements InputFilterProviderInterface{

	/**
	 *
	 * @param array $authors
	 * @throws \Zend\Form\Exception\InvalidArgumentException
	 */
	public function __construct(array $authors = array()){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

		$this->setAttribute('method', 'post');

		$this->add(array(
			'name' => 'title',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Titill...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Titill',
			),
		));

		$this->add(array(
			'name' => 'summary',
			'type' => 'Zend\Form\Element\Textarea',
			'attributes' => array(
				'placeholder' => 'Útdráttur...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Útdráttur',
			),
		));

		$this->add(array(
			'name' => 'body',
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
			'name' => 'venue',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Birtist fyrst...',
			),
			'options' => array(
				'label' => 'Birtist fyrst',
			),
		));

		$authorsArray = array();
		foreach($authors as $author){
			$authorsArray[$author->id] = $author->name;
		}
		$this->add(array(
			'name' => 'authors',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => array(
				'multiple' => 'multiple',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Höfundar',
				'value_options' => $authorsArray,
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
	 * Recursively populate value attributes of elements
	 *
	 * @param  array|\Traversable $data
	 * @return void
	 */
	public function populateValues($data){
		foreach($data as $key=>$row){
			if( $key == 'authors' && is_array($row) ){
				$data[$key] = array_map(function($i){
					return (is_numeric($i)) ? $i : $i->id;
				},$row);
			}
		}

		parent::populateValues($data);
	}


	/**
	 * Should return an array specification compatible with
	 * {@link Zend\InputFilter\Factory::createInputFilter()}.
	 *
	 * @return array
	 */
	public function getInputFilterSpecification(){
		return array(
			'title' => array(
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
			'summary' => array(
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
			'body' => array(
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
			'venue' => array(
				'required' => false,
				'allow_empty' => true,
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
			'authors' => array(),
		);
	}
}
