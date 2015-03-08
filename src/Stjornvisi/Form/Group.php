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
class Group extends Form implements InputFilterProviderInterface{

	public function __construct($action='create', $values=null, $options=array()){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Nafn faghóps...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Nafn faghóps',
            ),
        ));

        $this->add(array(
            'name' => 'name_short',
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
            'name' => 'summary',
            'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Inngangur.  Birtist á yfirlitssíðum.  Má ekki vera mjög langur texti.',
            ),
            'options' => array(
                'label' => 'Inngangur',
            ),
        ));

        $this->add(array(
            'name' => 'body',
            'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Lýsing faghópsins.  Birtist á síðu faghópsins sjálfs.  Inngangurinn birtist ekki þar.',
            ),
            'options' => array(
                'label' => 'Meginmál',
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Lýsing...',
            ),
            'options' => array(
                'label' => 'Lýsing',
            ),
        ));

        $this->add(array(
            'name' => 'description',
			'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Lýsing...',
            ),
            'options' => array(
                'label' => 'Lýsing',
            ),
        ));

        $this->add(array(
            'name' => 'objective',
			'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Markmið...',
            ),
            'options' => array(
                'label' => 'Markmið',
            ),
        ));

        $this->add(array(
            'name' => 'what_is',
			'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Hvað er...',
            ),
            'options' => array(
                'label' => 'Hvað er',
            ),
        ));

        $this->add(array(
            'name' => 'how_operates',
			'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Hvernig starfar...',
            ),
            'options' => array(
                'label' => 'Hvernig starfar',
            ),
        ));

        $this->add(array(
            'name' => 'for_whom',
			'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Fyrir hvern...',
            ),
            'options' => array(
                'label' => 'Fyrir hvern',
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
							'max'      => 45,
						),
					),
				),
			),
			'name_short' => array(
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
							'max'      => 45,
						),
					),
				),
			),
            'summary' => array(
                'required' => true,
                'allow_empty' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ),
            'body' => array(
                'required' => true,
                'allow_empty' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ),
			'description' => array(
				'required' => false,
				'allow_empty' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
			'objective' => array(
				'required' => false,
				'allow_empty' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
			'what_is' => array(
				'required' => false,
				'allow_empty' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
			'how_operates' => array(
				'required' => false,
				'allow_empty' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
			'for_whom' => array(
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

