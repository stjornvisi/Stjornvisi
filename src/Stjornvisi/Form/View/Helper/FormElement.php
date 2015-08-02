<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 28/01/15
 * Time: 16:36
 */

namespace Stjornvisi\Form\View\Helper;

use Stjornvisi\Form\Element\Rich;
use Stjornvisi\Form\Element\Img;
use Stjornvisi\Form\Element\File;
use Zend\Form\View\Helper\FormElement as BaseFormElement;
use Zend\Form\ElementInterface;

class FormElement extends BaseFormElement
{
    public function render(ElementInterface $element)
    {
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }

        if ($element instanceof Rich) {
            $helper = $renderer->plugin('richelement');
            return $helper($element);
        } elseif ($element instanceof Img) {
            $helper = $renderer->plugin('imgelement');
            return $helper($element);
        } elseif ($element instanceof File) {
            $helper = $renderer->plugin('fileelement');
            return $helper($element);
        }

        return parent::render($element);
    }
}
