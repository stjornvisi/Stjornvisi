<?php
namespace Stjornvisi\Controller;

use ArrayObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;

use Stjornvisi\Form\User as UserForm;
use Stjornvisi\Form\Password as PasswordForm;
/**
 * Company
 *
 * @category Stjornvisi
 * @package Controller
 * @author einar
 */
class UserController extends AbstractActionController{

	/**
	 * Get one user.
	 *
	 * @return array|ViewModel
	 */
	public function indexAction(){

        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $groupService = $sm->get('Stjornvisi\Service\Group');

        $auth = new AuthenticationService();

		//ACCESS GRANTED
		//
		if( $auth->hasIdentity() ){
			if( ( $user = $userService->get( $this->params()->fromRoute('id',0) ) ) != false ){
				return new ViewModel(array(
					'user'=> $user,
					'groups' => $groupService->getByUser( $user->id ),
					'attendance' => $userService->attendance( $user->id ),
					'access' => $userService->getTypeByUser(
							$user->id,
							($auth->hasIdentity())? $auth->getIdentity()->id : null
						),
				));
			}else{
				return $this->notFoundAction();
			}
		//ACCESS DENIED
		//
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}



		/*
		$userEntryDAO = new Application_Model_UserEntry();
		//RESOURCE FOUND
		//	found user
		if( ($user=$userEntryDAO->find($this->_getParam('id',Zend_Auth::getInstance()->getIdentity()->id))->current())!=null ){

			//ACCESS GRANTED
			//	user is this user or admin
			if( $this->_helper->acl()->validate(new Ext_Acl_User($user->id),Ext_Acl_User::RULE_READ) ){
				$this->view->user = $user;

				//AJAX
				//	xml http request
				if( $this->_request->isXmlHttpRequest() ){
					$this->getHelper('layout')->disableLayout();
					$this->getHelper('viewRenderer')->setNoRender();

					$this->_response->setBody($this->view->render("user/_partial-user-properties.phtml"));

					//NORMAL
					//	normal http request
				}else{
					$rangeObj = new Ext_Stjornvisi_DateRange();
					$currentRange = $rangeObj->getCurrentRange();
					$lastRange = $rangeObj->getLastRange();
						
					$statisticsDAO = new Application_Model_Statistics();
					$this->view->groupsAndEventsNow =
					$statisticsDAO->findGroupsAndEventsForUser(
							$this->view->user->id,
							$currentRange->rangeBegins->toString( 'YYYY-MM-dd'),
							$currentRange->rangeEnds->toString( 'YYYY-MM-dd') );
						
					$this->view->groupsAndEventsLast =
					$statisticsDAO->findGroupsAndEventsForUser(
							$this->view->user->id,
							$lastRange->rangeBegins->toString( 'YYYY-MM-dd' ),
							$lastRange->rangeEnds->toString( 'YYYY-MM-dd') );
				}

				//ACCESS DENIED
				//	user has no access
			}else{
				throw new Zend_Controller_Action_Exception("Access Denied",401);

			}

			//RESOURCE NOT FOUND
			//	found user
		}else{
			throw new Zend_Controller_Action_Exception("Resource Not Found",404);
		}
		*/
	}

	/**
	 * Change type of user. User|Admin.
	 *
	 * @return \Zend\Http\Response
	 */
	public function typeAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $auth = new AuthenticationService();
        $access = $userService->getTypeByUser(
            $this->params()->fromRoute('id'),
            ($auth->hasIdentity()) ? $auth->getIdentity()->id:null
        );
        //ACCESS GRANTED
        //
        if( $access->is_admin ){
            $userService->setType(
                $this->params()->fromRoute('id'),
                $this->params()->fromRoute('type')
            );
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
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
	 * List all users.
	 *
	 * @return ViewModel
	 */
	public function listAction(){
		$auth = new AuthenticationService();

		//ACCESS GRANTED
		//
		if( $auth->hasIdentity() ){
			$sm = $this->getServiceLocator();
			$userService = $sm->get('Stjornvisi\Service\User');

			return new ViewModel(array(
				'users'=> $userService->fetchAll()
			));
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
	 * Update user's properties.
	 *
	 * @return \Zend\Http\Response|ViewModel
	 */
	public function updateAction(){

        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $companyService = $sm->get('Stjornvisi\Service\Company');
        $valuesService = $sm->get('Stjornvisi\Service\Values');

        $auth = new AuthenticationService();

        //USER FOUND
        //  user found
        if( ( $user = $userService->get( $this->params()->fromRoute('id') ) ) != false ){

            $access = $userService->getTypeByUser(
                $user->id,
                ($auth->hasIdentity())?$auth->getIdentity()->id:null
            );

            //ACCESS GRANTED
            //
            if( $access->is_admin || $access->type == 1 ){
                $form = new UserForm($companyService->fetchAll(),$valuesService->getTitles());
                $form->setAttribute('action',$this->url()->fromRoute('notandi/update',array('id'=>$user->id)));

                //POST
                //  post request
                if( $this->request->isPost() ){
                    $form->setData( $this->request->getPost() );
                    //VALID FORM
                    //
                    if( $form->isValid() ){
                        return $this->redirect()->toRoute('notandi/index',array('id'=>$user->id));
                    //INVALID
                    //
                    }else{
						$this->getResponse()->setStatusCode(400);
                        return new ViewModel(array(
                            'form' => $form,
                            'user' => $user,
                        ));
                    }
                //QUERY
                //  get request
                }else{
                    $form->bind( new ArrayObject($user) );
                    return new ViewModel(array(
                        'form' => $form,
                        'user' => $user,
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


        //USER NOT FOUND
        //  404
        }else{
			return $this->notFoundAction();
        }
	}

	/**
	 * Change user's password.
	 *
	 * @return \Zend\Http\Response|ViewModel
	 * @todo url for form
	 */
	public function changePasswordAction(){

        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');

        //USER FOUND
        //  user found in storage
        if( ($user = $userService->get( $this->params()->fromRoute('id',0) )) != false ){
            $auth = new AuthenticationService();
            $access = $userService->getTypeByUser(
                $user->id,
                ( $auth->hasIdentity() )? $auth->getIdentity()->id : null
            );

            //ACCESS GRANTED
            //  granted
            if( $access->is_admin || $access->type == 1 ){

                $form = new PasswordForm();
                $form->setAttribute('action','');
                if( $this->request->isPost() ){
                    $form->setData( $this->request->getPost() );

                    //VALID
                    //  valid form
                    if( $form->isValid() ){

                        //PASS THE SAME
                        //  both input element contain the same string
                        if( $form->get('password')->getValue() == $form->get('password-again')->getValue() ){
                            $userService->setPassword($user->id,$form->get('password')->getValue());
                            return $this->redirect()->toRoute('notandi/index',array('id'=>$user->id));
                        //PASS MISMATCH
                        //
                        }else{
                            $form->get('password')->setMessages(array(
                                'Lykilorð passa ekki saman'
                            ));
                            return new ViewModel(array(
                                'form' => $form
                            ));
                        }
                    //INVALID
                    //  invalid form
                    }else{

                    }

                }else{
                    return new ViewModel(array(
                        'form' => $form
                    ));
                }

            //ACCESS DENIED
            //  no access
            }else{

            }

        //USER NOT FOUND
        }else{
			return $this->notFoundAction();
        }


        /*
		//LOGGED IN
		//	user is logged in
		if( Zend_Auth::getInstance()->hasIdentity() ){
			//POST
			//	post request
			if($this->_request->isPost()){

				//VALID
				//	valid form
				$form = new Application_Form_Password();
				if( $form->isValid($this->_request->getPost()) ){
					$userDAO = new Application_Model_User();
					$userDAO->update(
							array('passwd'=>new Zend_Db_Expr("MD5('{$form->getValue('pass1')}')")),
							"id=". Zend_Auth::getInstance()->getIdentity()->id);

					//INVALID
					//	form is invalid
				}else{
					$this->view->form = $form;
				}

				//GET
				//	query request
			}else{
				$this->view->form = new Application_Form_Password();
			}
		}else{
			throw new Zend_Controller_Action_Exception("Access Denied",401);
		}
        */
	}

	/**
	 * Get all groups by user.
	 *
	 * @return ViewModel
	 */
	public function groupsAction(){

		$auth = new AuthenticationService();

		$sm = $this->getServiceLocator();
		$groupService = $sm->get('Stjornvisi\Service\Group');
		/** @var $groupService \Stjornvisi\Service\Group */
		$groups = $groupService->userConnections( $auth->getIdentity()->id  );

		return new ViewModel(array(
			'groups' => $groups
		));
	}

	/**
	 *
	 * @todo implement
	 */
	public function deleteAction(){}

}



