<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 07/11/14
 * Time: 10:00
 */

namespace Stjornvisi\Form;

use Stjornvisi\Service\Company;
use Stjornvisi\Validator\UniqueSSN;
use Zend\Form\Form;
use Stjornvisi\Service\Values;
use Zend\InputFilter\InputFilterProviderInterface;

class NewUserIndividual extends Form implements InputFilterProviderInterface
{
    private $company;
    public function __construct(Values $values, Company $company)
    {
        $this->company = $company;
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'person-ssn',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => '000000-0000',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Kennitala',
            ),
        ));

        $this->add(array(
            'name' => 'person-address',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Ónefndgata 101',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Heimilisfang',
            ),
        ));

        $this->add(array(
            'name' => 'person-zip',
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
            'name' => 'submit-individual',
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
        return array(
            'person-ssn' => array(
                'validators' => array(
                    new UniqueSSN($this->company)
                ),
                'filters'  => array(
                    array('name' => 'Digits'),
                    array('name' => 'StringTrim'),
                ),
            ),
        );
    }
}
