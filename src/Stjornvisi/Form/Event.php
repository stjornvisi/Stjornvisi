<?php

namespace Stjornvisi\Form;

use Stjornvisi\Form\Element\Img as ImgElement;
use Zend\Form\Element\Text as TextElement;
use Zend\Form\Form;
use Zend\Validator\Callback;
use Zend\InputFilter\InputFilterProviderInterface;

class Event extends Form implements InputFilterProviderInterface
{
    const MAX_PRESENTERS = 3;

    public function __construct($groups = null)
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setHydrator(new Hydrator\Event());
        $this->setObject(new \stdClass());
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'subject',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Titill...',
                'required' => 'required',
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
            ),
            'options' => array(
                'label' => 'Heimilisfang:',
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
            'name' => 'event_date',
            'type' => 'Zend\Form\Element\Date',
            'attributes' => array(
                'placeholder' => 'YYYY-MM-DD',
                'required' => 'required',
                'step' => '1',
            ),
            'options' => array(
                'label' => 'Dagsetning',
            ),
        ));

        $this->add(array(
            'name' => 'event_time',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => '00:00',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Hefst',
            ),
        ));

        $this->add(array(
            'name' => 'event_end',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => '00:00',
                'required' => 'required',
            ),
            'options' => array(
                'label' => 'Líkur',
            ),
        ));

        $this->add(array(
            'name' => 'avatar',
            'type' => 'Stjornvisi\Form\Element\Img',
            'attributes' => array(
            ),
            'options' => array(
                'label' => 'Mynd',
            ),
        ));

        $this->addPresenters(self::MAX_PRESENTERS);

        $this->add(array(
            'name' => 'groups',
            'type' => 'Stjornvisi\Form\Element\MultiCheckbox',
            'attributes' => array(),
            'options' => array(
                'label' => 'Í samstarfi við',
                'value_options' => ($groups)? array_reduce($groups, function ($result, $item) {
                    $result[$item->id] = $item->name_short;
                    return $result;
                }):array(0),
            ),
        ));

        $this->add(array(
            'name' => 'lat',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'placeholder' => '64.1237224',
            ),
            'options' => array(
                'label' => 'Latitude (Breiddargráða)',
            ),
        ));

        $this->add(array(
            'name' => 'lng',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'placeholder' => '-21.9264241',
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

    public function populateValues($data)
    {
        foreach ($data as $key => $row) {
            if ($key=='groups') {
                $data[$key] = array_map(function ($i) {
                    return (is_numeric($i))?$i:$i->id;
                }, $row);
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
    public function getInputFilterSpecification()
    {
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
                                Callback::INVALID_VALUE => 'Viðburður getur ekki endað áður en hann byrjar',
                            ),
                            'callback' => function ($value, $context = array()) {
                                $from = new \DateTime($context['event_time']);
                                $to = new \DateTime($context['event_end']);
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

    private function addPresenters($num)
    {
        for ($i = 1; $i <= $num; ++$i) {
            $this->addPresenter($i);
        }
    }

    private function addPresenter($index)
    {
        $this->add([
            'name' => 'presenter' . $index,
            'type' => TextElement::class,
            'options' => [
                'label' => 'Fyrirlesari ' . $index,
            ],
        ]);

        $this->add([
            'name' => 'presenter' . $index . '_avatar',
            'type' => ImgElement::class,
            'options' => [
                'label' => 'Mynd af fyrirlesara ' . $index,
            ],
        ]);
    }
}
