<?php

namespace Stjornvisi\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Stjornvisi\Form\Hydrator;

class Event extends Form{

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
                'label' => 'Titill viðburðar',
            ),
        ));

        $this->add(array(
            'name' => 'body',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Meginmál',
            ),
        ));

        $this->add(array(
            'name' => 'location',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Type something...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Staðsetning: (Götuheiti og húsnúmer, Póstnúmer Bæjarfélag, t.d. Ofanleiti 2, 105 Reykjavík)',
            ),
        ));

        $this->add(array(
            'name' => 'event_date',
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
            'name' => 'event_time',
            //'type' => 'Zend\Form\Element\Time',
			'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Type something...',
                'required' => 'required',
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
                'placeholder' => 'Type something...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Viðburði líkur',
            ),
        ));

        $this->add(array(
            'name' => 'avatar',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
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
}
