<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 28/01/15
 * Time: 16:43
 */

namespace Stjornvisi\Form\View\Helper;

use Stjornvisi\Lib\SizeConvert;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\View\Helper\FormTextarea;

class RichElement extends FormTextarea
{
    public function render(ElementInterface $element)
    {
        $name   = $element->getName();
        if (empty($name) && $name !== 0) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $attributes         = $element->getAttributes();
        $attributes['name'] = $name;
        $content            = (string) $element->getValue();
        $escapeHtml         = $this->getEscapeHtmlHelper();


        //ADD OPTIONS
        //	this should really be in Stjonvisi\Form\Element\Img
        //	but it gets overwritten at some point, so the simplest
        //	thing was to add it here.
        //	TODO place this i a more generic place
        $element
            ->setOption('max', $this->getMaxSize())
            ->setOption('mime', '/image\/jpg|png|gif/')
            ->setOption('url', '/skrar/mynd');


        //OPTIONS
        //	options are used to set attributes and values
        //	to the custom element. We therefore need to remove
        //	label, label_attributes and label_options before we
        //	can convert them into an attribute string.
        $options = $element->getOptions();

        unset($options['label']);
        unset($options['label_attributes']);
        unset($options['label_options']);

        $strings = array_map(
            function ($key, $value) {
                return sprintf('%s="%s"', $key, $value);
            },
            array_keys($options),
            $options
        );

        return sprintf(
            '<stjornvisi-rich %s><textarea %s>%s</textarea></stjornvisi-rich>',
            implode(' ', $strings),
            $this->createAttributesString($attributes),
            $escapeHtml($content)
        );
    }

    /**
     * Get max upload size and return it as a
     * int byte number.
     *
     * @return int
     */
    private function getMaxSize()
    {
        $converter = new SizeConvert();
        return $converter->convert(ini_get('upload_max_filesize'));
    }
}
