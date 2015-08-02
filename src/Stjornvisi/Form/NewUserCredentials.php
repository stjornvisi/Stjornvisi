<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 07/11/14
 * Time: 10:00
 */

namespace Stjornvisi\Form;

use Stjornvisi\Service\Values;
use Stjornvisi\Service\User;
use Stjornvisi\Validator\UniqueEmail;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class NewUserCredentials extends Form implements InputFilterProviderInterface
{
    private $user;

    public function __construct(Values $values, User $user)
    {
        $this->user = $user;
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

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
            'name' => 'email',
            //'type' => 'Zend\Form\Element\Email',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Netfang.',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Netfang',
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'placeholder' => 'Titill',
            ),
            'options' => array(
                'label' => 'Titill',
                'empty_option' => 'Veldu starfstitil',
                'value_options' => $values->getTitles()
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
    public function getInputFilterSpecification()
    {
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
                            'max'      => 250,
                        ),
                    ),
                ),
            ),
            'email' => array(
                'validators' => array(
                    array(
                        'name'    => 'EmailAddress',
                        'messages' => array(
                            'emailAddressInvalidFormat' => 'Þetta er ekki löglegt netfang'
                        ),
                    ),
                    new UniqueEmail($this->user)
                ),
            ),
        );
    }
}
