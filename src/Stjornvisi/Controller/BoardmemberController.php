<?php

namespace Stjornvisi\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;

use Stjornvisi\Form\BoardMember as BoardMemberForm;
use Stjornvisi\Form\ConnectBoardMemberToBoard as ConnectBoardMemberToBoardForm;

class BoardmemberController extends AbstractActionController{

	/**
	 * List all boards from all times.
	 *
	 * @return ViewModel
	 */
	public function listAction(){
		$sm = $this->getServiceLocator();
		$boardService = $sm->get('Stjornvisi\Service\Board');
		$userService = $sm->get('Stjornvisi\Service\User');

		$auth = new AuthenticationService();

		$periods = $boardService->getBoards();
		return new ViewModel(array(
			'boards' => $periods,
			'access' => $userService->getType(( $auth->hasIdentity() )
					? $auth->getIdentity()->id
					: null),
		));

	}

	/**
	 * Create board member.
	 *
	 * @return HttpResponse|ViewModel
	 */
	public function createMemberAction(){
		$sm = $this->getServiceLocator();
		$boardService = $sm->get('Stjornvisi\Service\Board');
		$userService = $sm->get('Stjornvisi\Service\User');

		$auth = new AuthenticationService();
		$access = $userService->getType(( $auth->hasIdentity() )
			? $auth->getIdentity()->id
			: null);

		//ACCESS ALLOWED
		//
		if($access->is_admin){
			$form = new BoardMemberForm();
			$form->setAttribute('action',$this->url()->fromRoute('stjornin/create-member'));

			//POST
			//	post request
			if( $this->request->isPost() ){
				$form->setData( $this->request->getPost() );
				//VALID
				//	valid form
				if( $form->isValid() ){
					$boardService->createMember( $form->getData() );
					return $this->redirect()->toRoute('stjornin');
				//INVALID
				//	invalid form
				}else{
					$this->getResponse()->setStatusCode(400);
					return new ViewModel(array(
						'form' => $form
					));
				}
			//QUERY
			//	get request
			}else{
				return new ViewModel(array(
					'form' => $form
				));
			}
		//ACCESS DENIED
		//	403
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}
	}

	/**
	 * Update board member info.
	 *
	 * @return HttpResponse|ViewModel
	 */
	public function updateMemberAction(){
		$sm = $this->getServiceLocator();
		$boardService = $sm->get('Stjornvisi\Service\Board');
		$userService = $sm->get('Stjornvisi\Service\User');

		$auth = new AuthenticationService();
		$access = $userService->getType(( $auth->hasIdentity() )
			? $auth->getIdentity()->id
			: null);

		//ACCESS ALLOWED
		//
		if($access->is_admin){

			//FIND
			//
			if( ($member = $boardService->getMember($this->params()->fromRoute('id',0))) != false ){
				$form = new BoardMemberForm();
				$form->setAttribute(
					'action',
					$this->url()->fromRoute('stjornin/update-member',array('id'=>$member->id))
				);

				//POST
				//	post request
				if( $this->request->isPost() ){
					$form->setData( $this->request->getPost() );
					//VALID
					//	valid form
					if( $form->isValid() ){
						$boardService->updateMember( $member->id, $form->getData() );
						return $this->redirect()->toRoute('stjornin');
					//INVALID
					//	invalid form
					}else{
						$this->getResponse()->setStatusCode(400);
						return new ViewModel(array(
							'form' => $form
						));
					}
				//QUERY
				//	get request
				}else{
					$form->bind( new \ArrayObject($member) );
					return new ViewModel(array(
						'form' => $form
					));
				}
			//NOT FOUND
			//
			}else{
				return $this->notFoundAction();
			}


		//ACCESS DENIED
		//	403
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}
	}

	/**
	 * Connect member to term.
	 *
	 * @return HttpResponse|ViewModel
	 */
	public function connectMemberAction(){
		$sm = $this->getServiceLocator();
		$boardService = $sm->get('Stjornvisi\Service\Board');
		$userService = $sm->get('Stjornvisi\Service\User');

		$auth = new AuthenticationService();
		$access = $userService->getType(( $auth->hasIdentity() )
			? $auth->getIdentity()->id
			: null);

		//ACCESS ALLOWED
		//
		if($access->is_admin){
			$form = new ConnectBoardMemberToBoardForm(
				$boardService->getMembers(),
				$boardService->getTerms()
			);
			$form->setAttribute('action',$this->url()->fromRoute('stjornin/connect-member'));
			//POST
			//	post request
			if( $this->request->isPost() ){
				$form->setData( $this->request->getPost() );
				//VALID
				//	form valid
				if( $form->isValid() ){
					$boardService->connectMember( $form->getData() );
					return $this->redirect()->toRoute('stjornin');
				//INVALID
				//	invalid form
				}else{
					$this->getResponse()->setStatusCode(400);
					return new ViewModel(array(
						'form' => $form,
					));
				}
			//QUERY
			//	get request
			}else{
				return new ViewModel(array(
					'form' => $form,
				));
			}
		//ACCESS DENIED
		//
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}

	}

	/**
	 * Update board member connection.
	 *
	 * @return HttpResponse|ViewModel
	 */
	public function updateConnectMemberAction(){
		$sm = $this->getServiceLocator();
		$boardService = $sm->get('Stjornvisi\Service\Board');
		$userService = $sm->get('Stjornvisi\Service\User');

		$auth = new AuthenticationService();
		$access = $userService->getType(( $auth->hasIdentity() )
			? $auth->getIdentity()->id
			: null);

		//ACCESS ALLOWED
		//
		if($access->is_admin){

			//FOUND
			//	entry found
			if( ($connection = $boardService->getMemberConnection( $this->params()->fromRoute('id',0) )) != false ){
				$form = new ConnectBoardMemberToBoardForm(
					$boardService->getMembers(),
					$boardService->getTerms()
				);
				$form->setAttribute(
					'action',
					$this->url()->fromRoute('stjornin/update-connect-member',array('id'=>$connection->id))
				);
				//POST
				//	post request
				if( $this->request->isPost() ){
					$form->setData( $this->request->getPost() );
					//VALID
					//	valid form
					if($form->isValid()){
						$boardService->updateMemberConnection($connection->id,$form->getData());
						return $this->redirect()->toRoute('stjornin');
					//INVALID
					//	invalid form
					}else{
						$this->getResponse()->setStatusCode(400);
						return new ViewModel(array(
							'form' => $form
						));
					}
				//QUERY
				//	get request
				}else{
					$form->bind( new \ArrayObject($connection) );
					return new ViewModel(array(
						'form' => $form
					));
				}

			//NOT FOUND
			//	404
			}else{
				return $this->notFoundAction();
			}


		//ACCESS DENIED
		//	403
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}

	}

	/**
	 * Disconnect member from term.
	 *
	 * @return HttpResponse
	 */
	public function deleteConnectMemberAction(){
		$sm = $this->getServiceLocator();
		$boardService = $sm->get('Stjornvisi\Service\Board');
		$userService = $sm->get('Stjornvisi\Service\User');

		$auth = new AuthenticationService();
		$access = $userService->getType(( $auth->hasIdentity() )
			? $auth->getIdentity()->id
			: null);

		//ACCESS ALLOWED
		//
		if($access->is_admin){
			$boardService->disconnectMember(
				$this->params()->fromRoute('id',0)
			);
			return $this->redirect()->toRoute('stjornin');
		//ACCESS DENIED
		//
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}

	}

}
