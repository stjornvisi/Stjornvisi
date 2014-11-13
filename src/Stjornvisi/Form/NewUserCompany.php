<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 07/11/14
 * Time: 10:00
 */

namespace Stjornvisi\Form;

use Stjornvisi\Service\Values;
use Stjornvisi\Validator\Kennitala;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\InArray;

class NewUserCompany extends Form implements InputFilterProviderInterface  {

	private $values;
	public function __construct(Values $values){
		$this->values = $values;
		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

		$this->setAttribute('method', 'post');

		$this->add(array(
			'name' => 'company-name',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Nafn fyrirtækis.',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Nafn fyrirtækis',
			),
		));

		$this->add(array(
			'name' => 'company-ssn',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => '000000-0000',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Kennitala fyrirtækis',
			),
		));

		$this->add(array(
			'name' => 'company-address',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Ónefndgata 101',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Heimilisfang fyrirtækis',
			),
		));

		$this->add(array(
			'name' => 'company-zip',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => array(
				'placeholder' => 'Póstfang fyrirtækis.',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Póstfang fyrirtækis',
				'empty_option' => 'Veldu póstfang',
				'value_options' => $values->getPostalCode()
			),
		));

		$this->add(array(
			'name' => 'company-web',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'http://www.',
			),
			'options' => array(
				'label' => 'Heimasíða fyrirtækis',
			),
		));

		$this->add(array(
			'name' => 'company-size',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => array(
				'placeholder' => 'Fjöldi starfsmanna',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Fjöldi starfsmanna',
				'empty_option' => 'Veldu fjölda',
				'value_options' => $values->getCompanySizes()
			),
		));

		$this->add(array(
			'name' => 'company-type',
			'type' => 'Zend\Form\Element\Select',
			'attributes' => array(
				'placeholder' => 'Rekstrarfyrirkomulag',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Rekstrarfyrirkomulag',
				'empty_option' => 'Veldu rekstrarfyrirkomulag',
				'value_options' => $values->getBusinessTypes()
			),
		));

		$this->add(array(
			'name' => 'submit-company-create',
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
			'company-name' => array(
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
							'max'      => 60,
						),
					),
				),
			),
			// - - - - - - - - - - - - - - - - - - - - - - - - - - -
			/*
						'company-ssh' => array(

							'filters'  => array(
								//array('name' => 'Digits'),
								//array('name' => 'StringTrim'),
							),

							'validators' => array(
								array(
									'name'    => 'StringLength',
									'options' => array(
										'encoding' => 'UTF-8',
										'min'      => 10,
										'max'      => 11,
									),
								),
								new Kennitala(),
							),

			),
*/
			// - - - - - - - - - - - - - - - - - - - - - - - - - - -
			'company-address' => array(
				'filters'  => array(
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name'    => 'Alnum',
						'options' => array(
							'allowWhiteSpace' => true,
						),
					),
				),
			),
			// - - - - - - - - - - - - - - - - - - - - - - - - - - -
			'company-zip' => array(
				'validators' => array(
					(new InArray())->setHaystack( $this->values->getPostalCode() ),
				),
			),
			// - - - - - - - - - - - - - - - - - - - - - - - - - -
			'company-web' => array(
				'filters'  => array(
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name'    => 'Regex',
						'options' => array(
							'pattern' => '/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/'
						),
					),
				),
			),
			// - - - - - - - - - - - - - - - - - - - - - - - - - - -
			'company-size' => array(
				'validators' => array(
					(new InArray())->setHaystack( $this->values->getCompanySizes() ),
				),
			),
			// - - - - - - - - - - - - - - - - - - - - - - - - - -
			'company-type' => array(
				'validators' => array(
					(new InArray())->setHaystack( $this->values->getBusinessTypes() ),
				),
			),
			// - - - - - - - - - - - - - - - - - - - - - - - - - -
		);

	}
} 