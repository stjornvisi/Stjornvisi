<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 28/01/15
 * Time: 16:43
 */

namespace Stjornvisi\Form\View\Helper;


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

		return sprintf(
			'<stjornvisi-rich><textarea %s>%s</textarea></stjornvisi-rich>',
			$this->createAttributesString($attributes),
			$escapeHtml($content)
		);
	}
}