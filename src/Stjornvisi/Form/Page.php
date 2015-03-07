<?php
namespace Stjornvisi\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;
/**
 * Form for <Group>
 * 
 * @category Stjonvisi
 * @package Form
 * @author einarvalur
 *
 */
class Page extends Form implements InputFilterProviderInterface{

	public function __construct($action='create', $values=null, $options=array()){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

        $this->setAttribute('method', 'post');


        $this->add(array(
            'name' => 'label',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Stutt nafn...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Stutt nafn',
            ),
        ));

        $this->add(array(
            'name' => 'body',
			'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Lýsing...',
            ),
            'options' => array(
                'label' => 'Lýsing',
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
			'label' => array(
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
							'max'      => 255,
						),
					),
				),
			),
			'body' => array(
				'required' => false,
				'allow_empty' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
		);
	}

}

