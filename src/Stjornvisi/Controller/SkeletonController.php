<?php
/**
 * Created by PhpStorm.
 * User: hilmar
 * Date: 16/01/15
 * Time: 15:36
 */

namespace Stjornvisi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;

class SkeletonController extends AbstractActionController{
	public function indexAction(){
		$sm = $this->getServiceLocator();
		$skeletonService = $sm->get('Stjornvisi\Service\Skeleton'); /** @var $skeletonService \Stjornvisi\Service\Skeleton */

		return new ViewModel(
			array('skeleton' => $skeletonService->fetchAll() )
		);
	}
} 