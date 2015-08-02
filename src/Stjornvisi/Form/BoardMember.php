<?php

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class BoardMember extends Form implements InputFilterProviderInterface
{
    public function __construct($name = null)
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
                'label' => 'Nafn',
            ],
        ]);

        $this->add([
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => [
                'placeholder' => 'Netfang...',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Netfng',
            ],
        ]);

        $this->add([
            'name' => 'company',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Fyrirtæki...',
            ],
            'options' => [
                'label' => 'Fyrirtæki',
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

        $this->add([
            'name' => 'info',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => [
                'placeholder' => 'Upplýsingar...',
            ],
            'options' => [
                'label' => 'Upplýsingar',
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
            'email' => [
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    ['name' => 'EmailAddress'],
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
            'company' => [
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
                            'max'      => 100,
                        ],
                    ],
                ],
            ],
            'avatar' => [
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
                            'max'      => 255,
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

        ];
    }
}
