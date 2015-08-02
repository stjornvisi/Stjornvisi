<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 15/02/15
 * Time: 15:02
 */

namespace Stjornvisi\View\Strategy;

use Stjornvisi\View\Renderer\CsvRenderer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CsvFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CsvStrategy(new CsvRenderer());
    }
}
