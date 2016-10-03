<?php


namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class NewUserPassword extends Form
{
    public function __construct($name = null)
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Notendanafn...',
                'readonly' => 'readonly'
            ),
            'options' => array(
                'label' => 'Notendanafn',
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'placeholder' => 'Lykilorð...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Lykilorð',
            ),
        ));

        $this->add(array(
            'name' => 'password-again',
            'type' => 'Zend\Form\Element\Password',
            'attributes' => array(
                'placeholder' => 'Lykilorð aftur...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Lykilorð aftur',
            ),
            'validators' => array(
                array(
                    'name' => 'Identical',
                    'options' => array(
                        'token' => 'password', // name of first password field
                    ),
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
