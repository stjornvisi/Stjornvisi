<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 13/03/14
 * Time: 10:44
 */

namespace Stjornvisi\Form\Element;

class MultiCheckbox extends \Zend\Form\Element\MultiCheckbox
{
    /**
     * Provide default input rules for this element
     *
     * Attaches the captcha as a validator.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        $spec = array(
            'name' => $this->getName(),
            'required' => false,
        );

        if ($validator = $this->getValidator()) {
            $spec['validators'] = array(
                $validator,
            );
        }
        return $spec;
    }
}
