<?php
namespace Stjornvisi\Form;

use Zend\Form\Form;
/**
 * Form for <Group>
 * 
 * @category Stjonvisi
 * @package Form
 * @author einarvalur
 *
 */
class Group extends Form{

	public function __construct($action='create', $values=null, $options=array()){

		parent::__construct( strtolower( str_replace('\\','-',get_class($this) ) ));

        $this->setAttribute('method', 'post')
            ->setAttribute('action','/hopur/stofna');

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
            'name' => 'description',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'placeholder' => 'Lýsing...',
            ),
            'options' => array(
                'label' => 'Lýsing',
            ),
        ));

        $this->add(array(
            'name' => 'objective',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'placeholder' => 'Markmið...',
            ),
            'options' => array(
                'label' => 'Markmið',
            ),
        ));

        $this->add(array(
            'name' => 'what_is',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'placeholder' => 'Hvað er...',
            ),
            'options' => array(
                'label' => 'Hvað er',
            ),
        ));

        $this->add(array(
            'name' => 'how_operates',
            'type' => 'Zend\Form\Element\Textarea',
            'attributes' => array(
                'placeholder' => 'Hvernig starfar...',
            ),
            'options' => array(
                'label' => 'Hvernig starfar',
            ),
        ));

        $this->add(array(
            'name' => 'for_whom',
            'type' => 'Zend\Form\Element\Textarea',
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

        /*
		parent::__construct($options);
		
		//METHOD
		//	set method to form
		$this->setMethod('post');
		
		//ACTION
		//	set action form form
		if( $action=='update' ){
			$this->setAction('/group/update-description/id/'.$values->id);
		}else{
			$this->setAction('/group/create');
		}
		
		//NAME
		//	name of the group
		$nameElement = new Zend_Form_Element_Text('name');
		$nameElement->setRequired(true)
			->setValue( ($values)?$values->name:'' )
			->setLabel('Nafn faghóps');
		
		//SHORT NAME
		//	short name of the group
		$nameShortElement = new Zend_Form_Element_Text('name_short');
		$nameShortElement->setRequired(true)
			->setValue( ($values)?$values->name_short:'' )
			->setLabel('Stutt nafn');
		
		$descriptionElement = new Zend_Form_Element_Textarea("description");
		$descriptionElement
			->setLabel("Lýsing")
			->setValue( ($values)?$values->description:'' );
		
		$objectiveElement = new Zend_Form_Element_Textarea("objective");
		$objectiveElement
			->setLabel("Markmið")
			->setValue( ($values)?$values->objective:'' );
		
		$whatElement = new Zend_Form_Element_Textarea("what_is");
		$whatElement
			->setLabel("Hvað er")
			->setValue( ($values)?$values->what_is:'' );
		
		$howElement = new Zend_Form_Element_Textarea("how_operates");
		$howElement
			->setLabel("Hvernig starfar")
			->setValue( ($values)?$values->how_operates:'' );
		
		$forElement = new Zend_Form_Element_Textarea("for_whom");
		$forElement
			->setLabel("Fyrir hvern")
			->setValue( ($values)?$values->for_whom:'' );
		
		//SUBMIT
		//	submit create/update button
		$submitElement = new Zend_Form_Element_Submit('submit');
		$submitElement
			->setLabel( ($action=='create')?'Stofna':'Uppfæra' );
		
		//ADD
		//	add all elements to form
		$this->addElements(array(
			$nameElement,
			$nameShortElement,
			$descriptionElement,
			$objectiveElement,
			$whatElement,
			$howElement,
			$forElement,
			$submitElement
		));
        */
	}


}

