<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/23/14
 * Time: 1:41 PM
 */

namespace Stjornvisi\View\Strategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CsvFactory  implements FactoryInterface {
    public function createService(ServiceLocatorInterface $serviceLocator){
        $viewRenderer = $serviceLocator->get('ViewRenderer');
        return new CsvStrategy($viewRenderer);
    }
} 