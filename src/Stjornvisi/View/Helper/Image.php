<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 26/07/15
 * Time: 8:52 PM
 */

namespace Stjornvisi\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Stjornvisi\Properties\FileProperties;

class Image extends AbstractHelper
{
    public function __invoke($name, $size = FileProperties::DIR_MEDIUM, array $class = [])
    {
        $srcset = implode(', ', [
            $this->getView()->basePath(implode('/', [FileProperties::DIR_IMAGES, $size, '1x@'.$name]) . ' 1x'),
            $this->getView()->basePath(implode('/', [FileProperties::DIR_IMAGES, $size, '2x@'.$name]) . ' 2x'),
        ]);

        $src = $this->getView()->basePath(
            implode('/', [FileProperties::DIR_IMAGES, $size, '1x@'.$name])
        );

        $classes = implode(' ', $class);

        return sprintf('<img src="%s" srcset="%s" class="%s" />', $src, $srcset, $classes);

    }
}
