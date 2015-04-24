<?php

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class ConnectBoardMemberToBoard extends Form
{
	public function __construct($members = [], $terms = [])
    {

		parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

		$this->setAttribute('method', 'post');
		$memberValue = [];
		foreach ($members as $member) {
			$memberValue[$member->id] = $member->name;
		}
		$this->add([
			'name' => 'boardmember_id',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => [
				'required' => 'required',
			],
			'options' => [
				'label' => 'Meðlimur',
				'value_options' => $memberValue,
			],
		]);


		$this->add([
			'name' => 'term',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => [
				'required' => 'required',
			],
			'options' => [
				'label' => 'Tímabil',
				'value_options' => $terms,
			],
		]);

		$this->add([
			'name' => 'is_chairman',
			'type' => 'Zend\Form\Element\Checkbox',
			'attributes' => [
				'value' => '0',
			],
			'options' => [
				'label' => 'Formaður stjórnar',
				'value_options' => [
					'0' => 'Checkbox',
				],
			],
		]);

		$this->add([
			'name' => 'is_reserve',
			'type' => 'Zend\Form\Element\Checkbox',
			'attributes' => [
				'value' => '0',
			],
			'options' => [
				'label' => 'Varamaður í stjórn',
				'value_options' => [
					'0' => 'Checkbox',
				],
			],
		]);

		$this->add([
			'name' => 'is_manager',
			'type' => 'Zend\Form\Element\Checkbox',
			'attributes' => [
				'value' => '0',
			],
			'options' => [
				'label' => 'Framkvæmdarstjóri',
				'value_options' => [
					'0' => 'Checkbox',
				],
			],
		]);

		$this->add([
			'name' => 'submit',
			'type' => 'Zend\Form\Element\Submit',
			'attributes' => [
				'value' => 'Submit',
			],
			'options' => [
				'label' => 'Submit',
			],
		]);
	}
}
