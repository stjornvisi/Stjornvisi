<?php

namespace Stjornvisi\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Event extends Form implements InputFilterProviderInterface
{
    public function __construct($groups = null)
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setHydrator(new Hydrator());
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'subject',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Titill...',
                'required' => 'required',
                'tabindex' => 1
            ),
            'options' => array(
                'label' => 'Titill viðburðar',
            ),
        ));

        $this->add(array(
            'name' => 'body',
            'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => '...',
                'tabindex' => 2
            ),
            'options' => array(
                'label' => 'Meginmál',
            ),
        ));

        $this->add(array(
            'name' => 'location',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Landspítalinn, Hringsalur 1.hæð við Barnaspítal',
                'tabindex' => 3
            ),
            'options' => array(
                'label' => 'Staðsetning',
            ),
        ));

        $this->add(array(
            'name' => 'address',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Ofanleiti 2, 105 Reykjavík',
                'tabindex' => 4
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
                'tabindex' => 5
            ),
            'options' => array(
                'label' => 'Fjöldatakmörkun, 0 er ótakmarkað',
            ),
        ));

        $this->add(array(
            'name' => 'event_date',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => array(
                'placeholder' => 'Type something...',
                'required' => 'required',
                'step' => '1',
                'tabindex' => 6
            ),
            'options' => array(
                'label' => 'Dagsetning',
            ),
        ));

        $this->add(array(
            'name' => 'event_time',
            //'type' => 'Zend\Form\Element\Time',
			'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => '00:00',
                'required' => 'required',
				'tabindex' => 7
            ),
            'options' => array(
                'label' => 'Viðburður hefst',
            ),
        ));

        $this->add(array(
            'name' => 'event_end',
            //'type' => 'Zend\Form\Element\Time',
			'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
				'placeholder' => '00:00',
                'required' => 'required',
				'tabindex' => 8
            ),
            'options' => array(
                'label' => 'Viðburði líkur',
            ),
        ));

        $this->add(array(
            'name' => 'avatar',
            'type' => 'Stjornvisi\Form\Element\Img',
            'attributes' => array(
                'tabindex' => 9
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
            'name' => 'lat',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => '64.1237224',
                'tabindex' => 10
            ),
            'options' => array(
                'label' => 'Latitude (Breiddargráða)',
            ),
        ));

        $this->add(array(
            'name' => 'lng',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => '-21.9264241',
                'tabindex' => 10
            ),
            'options' => array(
                'label' => 'Longitude (Lengdargráða)',
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
					//array('name' => 'StripTags'),
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
			'lat' => array(
				'required' => false,
				'allow_empty' => true,
			),
			'lng' => array(
				'required' => false,
				'allow_empty' => true,
			),
		);
	}
}
