<?php
namespace Stjornvisi\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class News extends Form{

    public function __construct($name = null)
    {
		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Titill...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Titill',
            ),
        ));

        $this->add(array(
            'name' => 'body',
			'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Meginmál...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Meginmál'
            ),
        ));

        $this->add(array(
            'name' => 'avatar',
			'type' => 'Stjornvisi\Form\Element\Img',
            'attributes' => array(
				'data-url' => '/skrar/mynd'	//TODO can I use a function to call the router?
            ),
            'options' => array(
                'label' => 'Mynd',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                //'value' => 'Submit',
            ),
            'options' => array(
                'label' => 'Submit',
            ),
        ));

    }
}
