<?php

namespace Stjornvisi\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

/**
 * Class Article
 *
 * Form to create articles.
 *
 * @package Stjornvisi\Form
 */
class Article extends Form implements InputFilterProviderInterface
{
    /**
     *
     * @param array $authors
     * @throws \Zend\Form\Exception\InvalidArgumentException
     */
    public function __construct(array $authors = array())
    {
        parent::__construct(strtolower(str_replace('\\', '-', get_class($this))));

        $this->setAttribute('method', 'post');

        $this->add([
            'name' => 'title',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Titill...',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Titill',
            ],
        ]);

        $this->add([
            'name' => 'summary',
            'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => [
                'placeholder' => 'Útdráttur...',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Útdráttur',
            ],
        ]);

        $this->add([
            'name' => 'body',
            'type' => 'Stjornvisi\Form\Element\Rich',
            'attributes' => [
                'placeholder' => 'Texti...',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Texti',
            ],
        ]);

        $this->add([
            'name' => 'venue',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => [
                'placeholder' => 'Birtist fyrst...',
            ],
            'options' => array(
                'label' => 'Birtist fyrst',
            ),
        ]);

        $authorsArray = array();
        foreach ($authors as $author) {
            $authorsArray[$author->id] = $author->name;
        }
        $this->add([
            'name' => 'authors',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => [
                'multiple' => 'multiple',
                'required' => 'required',
            ],
            'options' => [
                'label' => 'Höfundar',
                'value_options' => $authorsArray,
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => [
                'value' => 'Submit',
            ],
            'options' => [
                'label' => 'Submit',
            ],
        ]);

    }

    /**
     * Recursively populate value attributes of elements
     *
     * @param  array|\Traversable $data
     * @return void
     */
    public function populateValues($data)
    {
        foreach ($data as $key => $row) {
            if ($key == 'authors' && is_array($row)) {
                $data[$key] = array_map(
                    function ($i) {
                        return (is_numeric($i)) ? $i : $i->id;
                    },
                    $row
                );
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
        return [
            'title' => array(
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ],
                    ],
                ],
            ),
            'summary' => [
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ],
            'body' => [
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
            ],
            'venue' => [
                'required' => false,
                'allow_empty' => true,
                'filters'  => [
                    ['name' => 'StripTags'],
                    ['name' => 'StringTrim'],
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 100,
                        ],
                    ],
                ],
            ],
            'authors' => [],
        ];
    }
}
