<?php

namespace Stjornvisi\Form;

use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Form;

class UserSubscriptions extends Form
{
    public function __construct($groups)
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));
        $this->setHydrator(new Hydrator());

        $this->setAttribute('method', 'post');

        $this->addGroups($groups);
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

    private function addGroups($groups)
    {
        $this->add(array(
            'name' => 'groups',
            'type' => 'Stjornvisi\Form\Element\MultiCheckbox',
            'attributes' => array(),
            'options' => array(
                'label' => 'Ég vil fá tölvupóst fyrir valda hópa:',
                'value_options' => ($groups) ? array_reduce($groups, function ($result, $item) {
                    $result[$item->group_id] = $item->name_short;
                    return $result;
                }) : array(0),
            ),
        ));
    }

    public function populateValues($data)
    {
        foreach ($data as $key => $row) {
            if ($key == 'groups') {
                $data[$key] = array_map(function ($i) {
                    return (is_numeric($i)) ? $i : $i->group_id;
                }, $row);
            }
        }

        parent::populateValues($data);
    }
}
