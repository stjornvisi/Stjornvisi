<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 12/03/14
 * Time: 10:38
 */

namespace Stjornvisi\Controller;

use Stjornvisi\Form\Page;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Client;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Class LocationController.
 *
 * @package Stjornvisi\Controller
 */
class LocationController extends AbstractActionController
{
    /**
     * Display one static page.
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        /** @var  $mapService \Stjornvisi\Service\MapInterface*/
        $mapService = $this->getServiceLocator()
            ->get('Stjornvisi\Service\Map');

        $mapResponse = $mapService->request($this->params()->fromQuery('q'));
        return new JsonModel([$mapResponse]);
    }
}
