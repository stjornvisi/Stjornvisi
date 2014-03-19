<?php


namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class Password extends Form{
    public function __construct($name = null)
    {
		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

        $this->setAttribute('method', 'post');

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

/**
 * 
 * @category Stjornvisi
 * @package Form
 * @author einarvalur
 */
/*
class Application_Form_Password extends Zend_Form{

	public function init(){
		$pass1Element = new Zend_Form_Element_Password("pass1");
		$pass1Element->setRequired(true)
			->setLabel("Lykilorð");
		$pass2Element = new Zend_Form_Element_Password("pass2");
		$pass2Element->setRequired(true)
			//TODO this doesn't work... why?
//			->setValidators(array(new Zend_Validate_Identical(array('token'=>'pass1','strict'=>false))))
			->setLabel("Lykilorð aftur");
			
		$submitElement = new Zend_Form_Element_Submit("submit");
		$submitElement->setLabel("uppfæra");
		
		$this->addElements(array(
			$pass1Element,
			$pass2Element,
			$submitElement
		));
		
	}


}
*/
