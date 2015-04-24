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

class Author extends Form implements InputFilterProviderInterface
{

	public function __construct()
    {
		parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

		$this->setAttribute('method', 'post');

		$this->add([
			'name' => 'name',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => [
				'placeholder' => 'Nafn...',
				'required' => 'required',
			],
			'options' => [
				'label' => 'Titill',
			],
		]);

		$this->add([
			'name' => 'info',
			'type' => 'Stjornvisi\Form\Element\Rich',
			'attributes' => [
				'placeholder' => 'Texti...',
			],
			'options' => [
				'label' => 'Texti',
			],
		]);

		$this->add([
			'name' => 'avatar',
			'type' => 'Stjornvisi\Form\Element\Img',
			'attributes' => [
				'placeholder' => 'Mynd...',
			],
			'options' => [
				'label' => 'Mynd',
			],
		]);

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
	public function getInputFilterSpecification()
    {
		return [
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
							'max'      => 100,
						],
					],
				],
			],
			'info' => [
				'required' => false,
				'allow_empty' => true,
				'filters'  => [
					['name' => 'StripTags'],
					['name' => 'StringTrim'],
				],
			],
			'avatar' => [
				'required' => false,
				'allow_empty' => true,
				'filters'  => [
					['name' => 'StripTags'],
					['name' => 'StringTrim'],
				],
			],
		];
	}
}
