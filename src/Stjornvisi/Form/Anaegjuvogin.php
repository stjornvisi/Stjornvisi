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
class Anaegjuvogin extends Form implements InputFilterProviderInterface
{

	/**
	 *
	 * @param array $authors
	 * @throws \Zend\Form\Exception\InvalidArgumentException
	 */
	public function __construct(array $authors = array())
	{
		parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

		$this->setAttribute('method', 'post');

		$this->add([
			'name' => 'name',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Titill...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Titill',
			),
		]);

		$this->add([
			'name' => 'year',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => array(
				'placeholder' => '2015',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Ár',
				'value_options' => $this->getYearRange()
			),
		]);

		$this->add([
			'name' => 'body',
			'type' => 'Stjornvisi\Form\Element\Rich',
			'attributes' => array(
				'placeholder' => 'Texti...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Texti',
			),
		]);

		$this->add([
			'name' => 'submit',
			'type' => 'Zend\Form\Element\Submit',
			'attributes' => array(
				'value' => 'Submit',
			),
			'options' => array(
				'label' => 'Submit',
			),
		]);
	}

	/**
	 * Should return an array specification compatible with
	 * {@link Zend\InputFilter\Factory::createInputFilter()}.
	 *
	 * @return array
	 */
	public function getInputFilterSpecification()
	{
		return array(
			'name' => [
				'filters'  => [
					['name' => 'StripTags'],
					['name' => 'StringTrim'],
				],
				'validators' => [
					[
						'name'    => 'StringLength',
						'options' => [
							'encoding' => 'UTF-8',
							'min'      => 1,
							'max'      => 255,
						],
					],
				],
			],

			'body' => [
				'filters'  => [
					array('name' => 'StringTrim'),
				],
			],

			'year' => [
				'required' => true,
				'allow_empty' => true,
				'filters' => [
					[
						'name' => 'Null'
					],
				],
			],
		);
	}

	/**
	 * Create value options for YEAR element.
	 *
	 * @return array
	 */
	private function getYearRange()
	{
		$years = ['' => 'Forsida'];
		foreach (range(2000, (date('Y')+2)) as $year) {
			$years[$year] = $year;
		}
		return array_reverse($years, true);
	}
}
