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
class Group extends Form implements InputFilterProviderInterface
{
    public function __construct($action = 'create', $values = null, $options = array())
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));
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
            'name' => 'avatar',
            'type' => 'Stjornvisi\Form\Element\Img',
            'attributes' => array(

            ),
            'options' => array(
                'label' => 'Mynd',
            ),
        ));

        $this->add([
            'name' => 'hidden',
            'type' => 'Zend\Form\Element\Select',
            'options' => [
                'label' => 'Falin hópur',
                'value_options' => [
                    '0' => 'Nei',
                    '1' => 'Já',
                ],
            ],
        ]);

        $this->add(array(
            'name' => 'summary',
            'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Inngangur.  100 - 500 slög',
                'maxlength' => 350,
                'rows' => 4,
            ),
            'options' => array(
                'label' => 'Inngangur',
                'description' => 'Lýsing á faghópi og starfi hans ætti að halda við 3 línur/50 orð. Hámarksfjöldi stafa er 350.',
            ),
        ));

        $this->add(array(
            'name' => 'body',
            'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => array(
                'placeholder' => 'Lýsing faghópsins.',
            ),
            'options' => array(
                'label' => 'Meginmál',
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
    public function getInputFilterSpecification()
    {
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
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 100,
                            'max'      => 500,
                        ),
                    ),
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
        );
    }
}
