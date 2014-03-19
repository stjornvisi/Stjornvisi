<?php

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class ConnectBoardMemberToBoard extends Form{

	public function __construct($members = array(), $terms = array()){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

		$this->setAttribute('method', 'post');
		$memberValue = array();
		foreach($members as $member){
			$memberValue[$member->id] = $member->name;
		}
		$this->add(array(
			'name' => 'boardmember_id',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => array(
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Meðlimur',
				'value_options' => $memberValue,
			),
		));


		$this->add(array(
			'name' => 'term',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => array(
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Tímabil',
				'value_options' => $terms,
			),
		));

		$this->add(array(
			'name' => 'is_chairman',
			'type' => 'Zend\Form\Element\Checkbox',
			'attributes' => array(
				'value' => '0',
			),
			'options' => array(
				'label' => 'Formaður stjórnar',
				'value_options' => array(
					'0' => 'Checkbox',
				),
			),
		));

		$this->add(array(
			'name' => 'is_reserve',
			'type' => 'Zend\Form\Element\Checkbox',
			'attributes' => array(
				'value' => '0',
			),
			'options' => array(
				'label' => 'Varamaður í stjórn',
				'value_options' => array(
					'0' => 'Checkbox',
				),
			),
		));

		$this->add(array(
			'name' => 'is_manager',
			'type' => 'Zend\Form\Element\Checkbox',
			'attributes' => array(
				'value' => '0',
			),
			'options' => array(
				'label' => 'Framkvæmdarstjóri',
				'value_options' => array(
					'0' => 'Checkbox',
				),
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
