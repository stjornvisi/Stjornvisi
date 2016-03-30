<?php

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class User extends Form
{
    public function __construct($companies = array(), $titles = array())
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'name',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Nafn...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Nafn',
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'placeholder' => 'Netfang...',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Netfang',
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Titill',
                'value_options' => $titles,
            ),
        ));

        $companyArray = array();
        foreach ($companies as $company) {
            $companyArray[$company->id] = $company->name;
        }
        $this->add(array(
            'name' => 'company',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Fyrirtæki',
                'value_options' => $companyArray,
            ),
        ));

        $this->addNotificationBoxes();

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

    private function addNotificationBoxes()
    {
        $fields = $this->getNotificationFields();
        foreach ($fields as $field => $label) {
            $this->add((new Element\Checkbox())
                ->setName($field)
                ->setLabel($label)
            );
        }
    }

    /**
     * @return array
     */
    public function getNotificationFields()
    {
        $fields = [
            'email_event_upcoming' => 'Viðburðir á næstunni',
            'email_global_all' => 'Alla notendur',
            'email_group_manager' => 'Faghópar þar sem ég er í stjórn (sent á stjórn)',
            'email_group_all' => 'Faghóparnir mínir',
            'email_event_all' => 'Viðburðir í mínum faghóp',
            'email_event_participant' => 'Viðburðir sem ég mæti á',
            'email_global_manager' => 'Allir í stjórnum faghópa',
            'email_global_chairman' => 'Allir formenn stjórna faghópa',
        ];
        return $fields;
    }
}
