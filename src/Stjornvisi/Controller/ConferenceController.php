<?php
/**
 * Created by PhpStorm.
 * User: hilmar
 * Date: 17/01/15
 * Time: 19:35
 */

namespace Stjornvisi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;

class ConferenceController extends AbstractActionController {
	public function listAction(){
		$sm = $this->getServiceLocator();
		$conferenceService = $sm->get('Stjornvisi\Service\Conference'); /** @var $skeletonService \Stjornvisi\Service\Conference */

		return new ViewModel(
			array('conferences' => $conferenceService->fetchAll() )
		);
	}
} 