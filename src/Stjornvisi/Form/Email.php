<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/2/14
 * Time: 1:23 PM
 */

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class Email extends Form
{
    public function __construct($name = null)
    {

		parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setAttribute('method', 'post');

        $this->add([
            'name' => 'subject',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Efni',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Efni',
            ],
        ]);

        $this->add([
            'name' => 'body',
			'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => [
                'placeholder' => 'Texti',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Texti',
				'bucket' => 'path'
            ],
        ]);

        $this->add([
            'name' => 'send',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => [
                'value' => 'Senda',
            ],
            'options' => [
                'label' => 'Senda'
            ],
        ]);

        $this->add([
            'name' => 'test',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => [
                'value' => 'Prufa',
            ],
            'options' => [
                'label' => 'Prufa'
            ],
        ]);

    }
}
