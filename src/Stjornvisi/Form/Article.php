<?php

namespace Stjornvisi\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class Article extends Form{

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
				'required' => 'required',
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

	public function populateValues($data){
		foreach($data as $key=>$row){
			if( $key=='authors' ){
				$data[$key] = array_map(function($i){
					return (is_numeric($i))?$i:$i->id;
				},$row);
			}
		}

		parent::populateValues($data);
	}
}
