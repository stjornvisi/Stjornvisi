<?php
/**
 * Created by PhpStorm.
 * User: hilmar
 * Date: 17/01/15
 * Time: 19:35
 */

namespace Stjornvisi\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;

class ConferenceController extends AbstractActionController {
	public function listAction(){
		$sm = $this->getServiceLocator();
		$conferenceService = $sm->get('Stjornvisi\Service\Conference'); /** @var $conferenceService \Stjornvisi\Service\Conference */

		return new ViewModel(
			array('conferences' => $conferenceService->fetchAll() )
		);
	}

	public function indexAction(){
		$sm = $this->getServiceLocator();
		$conferenceService = $sm->get('Stjornvisi\Service\Conference'); /** @var $conferenceService \Stjornvisi\Service\Conference */
		$userService = $sm->get('Stjornvisi\Service\User');

		$auth = new AuthenticationService();

		if ( ( $conference = $conferenceService->get($this->params()->fromRoute('id',0)) ) != false ) {
			return new ViewModel(array(
				'conference' => $conference,
				'access' => $userService->getType(( $auth->hasIdentity() )
						? $auth->getIdentity()->id
						: null)
			));
		} else {
			$this->getResponse()->setStatusCode(404);
			return;
		}
	}
} 