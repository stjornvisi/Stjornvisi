<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 15/02/15
 * Time: 15:02
 */

namespace Stjornvisi\View\Strategy;

use Stjornvisi\View\Renderer\IcalRenderer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IcalFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new IcalStrategy(new IcalRenderer());
    }
}
