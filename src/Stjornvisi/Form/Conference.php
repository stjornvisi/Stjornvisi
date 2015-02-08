<?php

namespace Stjornvisi\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Conference extends Form implements InputFilterProviderInterface{

	public function __construct($groups = null){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

		$this->setHydrator( new Hydrator() );
		$this->setAttribute('method', 'post');

		$this->add(array(
			'name' => 'subject',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Type something...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Titill ráðstefnu',
			),
		));

		$this->add(array(
			'name' => 'body',
			'type' => 'Zend\Form\Element\Textarea',
			'attributes' => array(

			),
			'options' => array(
				'label' => 'Meginmál',
			),
		));

		$this->add(array(
			'name' => 'location',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Harpa, Ráðstefnuhús',
			),
			'options' => array(
				'label' => 'Staðsetning',
			),
		));

		$this->add(array(
			'name' => 'address',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Austurbakki 2, 101 Reykjavík',
			),
			'options' => array(
				'label' => 'Heimilisfang: (Götuheiti og húsnúmer, Póstnúmer Bæjarfélag)',
			),
		));

		$this->add(array(
			'name' => 'capacity',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => '0',
			),
			'options' => array(
				'label' => 'Fjöldatakmörkun, 0 er ótakmarkað',
			),
		));

		$this->add(array(
			'name' => 'conference_date',
			'type' => 'Zend\Form\Element\Date',
			'attributes' => array(
				'placeholder' => 'Type something...',
				'required' => 'required',
				'step' => '1',
			),
			'options' => array(
				'label' => 'Dagsetning',
			),
		));

		$this->add(array(
			'name' => 'conference_time',
			//'type' => 'Zend\Form\Element\Time',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Type something...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Ráðstefnan hefst',
			),
		));

		$this->add(array(
			'name' => 'conference_end',
			//'type' => 'Zend\Form\Element\Time',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'placeholder' => 'Type something...',
				'required' => 'required',
			),
			'options' => array(
				'label' => 'Ráðstefnunni líkur',
			),
		));

		$this->add(array(
			'name' => 'avatar',
			'type' => 'Zend\Form\Element\Text',
			'attributes' => array(
				'data-url' => '/skrar/mynd'	//TODO can I use a function to call the router?
			),
			'options' => array(
				'label' => 'Mynd',
			),
		));

		$this->add(array(
			'name' => 'groups',
			'type' => 'Stjornvisi\Form\Element\MultiCheckbox',
			'attributes' => array(),
			'options' => array(
				'label' => 'Í samstarfi við',
				'value_options' => ($groups)? array_reduce($groups,function($result, $item){
						$result[$item->id] = $item->name_short;
						return $result;
					}):array(0),
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

	public function populateValues($data){
		foreach($data as $key=>$row){
			if( $key=='groups' ){
				$data[$key] = array_map(function($i){
					return (is_numeric($i))?$i:$i->id;
				},$row);
			}
		}

		parent::populateValues($data);
	}

	/**
	 * Should return an array specification compatible with
	 * {@link Zend\InputFilter\Factory::createInputFilter()}.
	 *
	 * @return array
	 */
	public function getInputFilterSpecification(){
		return array(
			'subject' => array(
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
							'max'      => 100,
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
			'location' => array(
				'required' => false,
				'allow_empty' => true,
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
			'address' => array(
				'required' => false,
				'allow_empty' => true,
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
			'capacity' => array(
				'required' => false,
				'allow_empty' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name'    => 'Int',
						/*
						'options' => array(
							'encoding' => 'UTF-8',
							'min'      => 1,
							'max'      => 255,
						),
						*/
					),
				),
			),
			'event_date' => array(
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name'    => 'Date',
					),
				),
			),
			'event_time' => array(
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name'    => 'Date',
						'options' => array(
							'format' => 'H:i',
						),
					),
				),
			),
			'event_end' => array(
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
				'validators' => array(
					array(
						'name'    => 'Date',
						'options' => array(
							'format' => 'H:i',
						),
					),
					array(
						'name' => 'Callback',
						'options' => array(
							'messages' => array(
								\Zend\Validator\Callback::INVALID_VALUE => 'Viðburður getur ekki endað áður en hann byrjar',
							),
							'callback' => function( $value, $context=array() ){
									$from = new \DateTime( $context['event_time'] );
									$to = new \DateTime( $context['event_end'] );
									return $to > $from;
								},
						)
					)
				),
			),
			'avatar' => array(
				'required' => false,
				'allow_empty' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
				),
			),
			'groups' => array(
				'required' => false,
				'allow_empty' => true,
			),
		);
	}
}
