<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/13/14
 * Time: 11:06 PM
 */

namespace Stjornvisi\Form;


use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

use Stjornvisi\Filter\Ssn as SsnFilter;
use Stjornvisi\Validator\Kennitala as SsnValidator;
use Stjornvisi\Service\Values;

class Company extends Form implements InputFilterProviderInterface{

	private $company;
	private $values;

    public function __construct(Values $values, Company $company = null){
		$this->company = $company;
		$this->values = $values;
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
            'name' => 'ssn',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Kennitala...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Kennitala',
            ),
        ));

        $this->add(array(
            'name' => 'address',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Heimilisfang...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Heimilisfang',
            ),
        ));

        $this->add(array(
            'name' => 'zip',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Póstfang',
                'value_options' => $values->getPostalCode(),
            ),
        ));

        $this->add(array(
            'name' => 'business_type',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Rekstrarform',
                'value_options' => $values->getBusinessTypes(),
            ),
        ));

        $this->add(array(
            'name' => 'number_of_employees',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Starfsmannafjöldi',
                'value_options' => $values->getCompanySizes(),
            ),
        ));

        $this->add(array(
            'name' => 'website',
            'type' => 'Zend\Form\Element\Url',
            'attributes' => array(
                'placeholder' => 'Heimasíða...',
            ),
            'options' => array(
                'label' => 'Heimasíða',
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
							'max'      => 60,
						),
					),
				),
			),
			'ssn' => array(
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
					array( new SsnFilter() ),
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
			'address' => array(
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
							'max'      => 50,
						),
					),
				),
			),

		);
	}
} 
