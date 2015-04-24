<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/13/14
 * Time: 11:06 PM
 */

namespace Stjornvisi\Form;

use Stjornvisi\Validator\UniqueSSN;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

use Stjornvisi\Filter\Ssn as SsnFilter;
use Stjornvisi\Validator\Kennitala as SsnValidator;
use Stjornvisi\Service\Values;
use Stjornvisi\Service\Company as CompanyService;

class Company extends Form implements InputFilterProviderInterface
{
	private $company;
	private $values;

	private $id = null;

    public function __construct(Values $values, CompanyService $company = null)
    {
		$this->company = $company;
		$this->values = $values;
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
                'label' => 'Nafn',
            ],
        ]);

        $this->add([
            'name' => 'ssn',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => '000000-0000',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Kennitala',
            ],
        ]);

        $this->add([
            'name' => 'address',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Heimilisfang...',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Heimilisfang',
            ],
        ]);

        $this->add([
            'name' => 'zip',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Póstfang',
                'value_options' => $values->getPostalCode(),
            ],
        ]);

        $this->add([
            'name' => 'business_type',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Rekstrarform',
                'value_options' => $values->getBusinessTypes(),
            ],
        ]);

        $this->add([
            'name' => 'number_of_employees',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Starfsmannafjöldi',
                'value_options' => $values->getCompanySizes(),
            ],
        ]);

        $this->add([
            'name' => 'website',
            'type' => 'Zend\Form\Element\Url',
            'attributes' => [
                'placeholder' => 'http://',
            ],
            'options' => [
                'label' => 'Heimasíða',
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

	/**
	 * Set ID og the record being edited.
	 *
	 * This is required for the unique-snn validator.
	 * If the validator would only allow for valid form it the SSN
	 * in not in storage, we could not UPDATE a record. we have to
	 * allow for the same SSN if this SSN is connected to THIS record.
	 *
	 * To know id this is THIS record, we need the Identifier..
	 * therefor; this method :)
	 *
	 * @param $id
	 * @return $this
	 */
	public function setIdentifier($id)
    {
		$this->id = $id;
		return $this;
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
							'max'      => 60,
						],
					],
				],
			],
			'ssn' => [
				'filters'  => [
					['name' => 'Digits'],
					['name' => 'StringTrim'],
				],
				'validators' => [
					new UniqueSSN($this->company, $this->id),
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
			'address' => [
				'required' => false,
				'allow_empty' => true,
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
							'max'      => 50,
						],
					],
				],
			],
			'website' => [
				'required' => false,
				'allow_empty' => true,
			],
		];
	}
}
