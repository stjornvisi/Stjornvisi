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
use Stjornvisi\Form\Conference as ConferenceForm;

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

	/**
	 * Create one conference.
	 *
	 * @return string|\Zend\Http\Response|ViewModel
	 */
	public function createAction(){
		$sm = $this->getServiceLocator();
		$groupService = $sm->get('Stjornvisi\Service\Group');
		$userService = $sm->get('Stjornvisi\Service\User');
		$conferenceService = $sm->get('Stjornvisi\Service\Conference');
		$group_id = $this->params()->fromRoute('id', false);
		$form = new ConferenceForm( $groupService->fetchAll() );

		$authService = new AuthenticationService();
		$access = $userService->getTypeByGroup(
			($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
			$group_id
		);

		//GLOBAL EVENT
		//  this is a global event, only admin has access
		if( $group_id === false ){
			//ACCESS DENIED
			if(!$access->is_admin){
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
				//ACCESS GRANTED
				//
			}else{
				$form->setAttribute('action',$this->url()->fromRoute('radstefna/create'));
			}
			//GROUPS EVENT
			//  this is a group event accessible to admin and group
			//  managers
		}else{
			//ACCESS GRANTED
			//  user is admin or manager
			if($access->is_admin || $access->type >= 1){
				$form->setAttribute('action', $this->url()->fromRoute('radstefna/create',array('id'=>$group_id)) );
				$form->bind( new \ArrayObject( array('groups'=>array($group_id) )));
				//ACCESS DENIED
				//  user is not a manager or admin
			}else{
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
			}
		}


		//POST
		if($this->request->isPost() ){
			$form->setData($this->request->getPost());
			if( $form->isValid() ){
				$data = (array)$form->getData();
				unset($data['submit']);


				$mapService = $sm->get('Stjornvisi\Service\Map');
				/** @var  $mapService \Stjornvisi\Service\JaMap */
				$mapResult = $mapService->request( isset($data['address']) ? $data['address']: null );
				$data['lat'] = $mapResult->lat;
				$data['lng'] = $mapResult->lng;

				$id = $conferenceService->create( $data );
				return $this->redirect()->toRoute('radstefna/index',array('id'=>$id));
			}else{
				$this->getResponse()->setStatusCode(400);
				return new ViewModel(array(
					'form' => $form
				));
			}
			//QUERY
		}else{
			return new ViewModel(array(
				'form' => $form
			));
		}

	}
} 