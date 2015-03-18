<?php

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class UserGroups extends Form{
	public function __construct($groups = null){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));
		$this->setHydrator( new Hydrator() );
        $this->setAttribute('method', 'post');

		$this->add(array(
			'name' => 'groups',
			'type' => 'Stjornvisi\Form\Element\MultiCheckbox',
			'attributes' => array(),
			'options' => array(
				'label' => 'Í samstarfi við',
				'value_options' => ($groups)? array_reduce($groups,function($result, $item){
						$result[$item->group_id] = $item->name_short;
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
					return (is_numeric($i))?$i:$i->group_id;
				},$row);
			}
		}

		parent::populateValues($data);
	}
}
