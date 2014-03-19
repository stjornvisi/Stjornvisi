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
				var_dump('404');
			}
		//ACCESS DENIED
		//
		}else{
			var_dump('403');
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
            var_dump('403');
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
			var_dump('403');
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
                $form->setAttribute('action',"/notandi/{$user->id}/uppfaera");

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
                var_dump('403');
            }


        //USER NOT FOUND
        //  404
        }else{
            var_dump('404');
        }


        /*
		$userEntryDAO = new Application_Model_UserEntry();
		//RESOURCE FOUND
		//	found user
		if( ($user=$userEntryDAO->find($this->_getParam('id'))->current())!=null ){
				
			//ACCESS GRANTED
			//	user has access
			if( $this->_helper->acl()->validate(new Ext_Acl_User($user->id),Ext_Acl_User::RULE_READ) ){


				//POST
				//	post request
				if( $this->_request->isPost() ){

					$companyDAO = new Application_Model_Company();
					$form = new Application_Form_User('update',null,$companyDAO->fetchAll(null,"name"));
					//VALID
					//	form and it's data are valid
					if($form->isValid($this->_request->getPost())){
						$userDAO = new Application_Model_User();
						$userDAO->update(array(
								'name' => $form->getValue('name'),
								'email' => $form->getValue('email'),
								'title' => $form->getValue('title')
						), "id=".$user->id);
							
						//Add the new username to the session
						if( Zend_Auth::getInstance()->getIdentity()->id == $user->id ){
							$authobj = Zend_Auth::getInstance()->getStorage()->read();
							$authobj->name = $form->getValue('name');
						}


						//COMPANY
						//	update where the user works
						$userCompanyDAO = new Application_Model_CompanyHasUser();
						$userCompanyDAO->delete("user_id=".$user->id);
						$userCompanyDAO->insert(array(
								'user_id'=>$user->id,
								'company_id'=>$form->getValue("company"),
								'key_user' => 0
						));

						//AJAX OR NOT
						//	adjust the responce accordingly
						if($this->_request->isXmlHttpRequest()){
							$this->getHelper('layout')->disableLayout();
							$this->getHelper('viewRenderer')->setNoRender();
							$this->_forward("index","user","default");
						}else{
							$this->_redirect("/notandi");
						}

						//INVALID
						//	the form is invalid
					}else{
						if($this->_request->isXmlHttpRequest()){
							$this->getHelper('layout')->disableLayout();
							$this->getHelper('viewRenderer')->setNoRender();
							$this->_response->setBody((string)$form);
						}else{
							$this->view->form = $form;
						}
					}

					//GET
					//	query request
				}else{
					$userDAO = new Application_Model_UserEntry();
					$companyDAO = new Application_Model_Company();
					if( $this->_request->isXmlHttpRequest() ){
						$this->getHelper('layout')->disableLayout();
						$this->getHelper('viewRenderer')->setNoRender();
						$this->_response->setBody((string)new Application_Form_User(
								'update',
								$user,
								$companyDAO->fetchAll(null,"name")
						));
					}else{
						$this->view->form = new Application_Form_User(
								'update',
								$user,
								$companyDAO->fetchAll(null,"name")
						);
					}
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
	 * Change user's password.
	 *
	 * @return \Zend\Http\Response|ViewModel
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
            var_dump('404');
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
	 *
	 * @throws Zend_Controller_Action_Exception
	 * @todo need to have alook af cascationg effect of deleting a user
	 * @todo admin is redirected to list, user is redirected to logout
	 */
	public function deleteAction(){

		$userDAO = new Application_Model_User();
		//RESOURCE FOUND
		//	user found
		if( ($user=$userDAO->find($this->_getParam('id'))->current())!=null ){

			//ACCESS GRANTED
			//	user has access
			if( $this->_helper->acl()->validate(new Ext_Acl_User($user->id)) ){
				$userDAO->delete("id={$user->id}");
				if( Zend_Auth::getInstance()->getIdentity()->is_admin ){
					$this->_redirect("/admin/all-users/");
				}else{
					$this->_redirect("/utskra");
				}
				//ACCESS DENIED
				//	user has no access
			}else{
				throw new Zend_Controller_Action_Exception("Access Denied",401);
			}
				
			//RESOURCE NOT FOUND
			//	user not found
		}else{
			throw new Zend_Controller_Action_Exception("Resource Not Found",404);
		}

	}

	/**
	 * @todo but what if the user is already logged in?
	 */
	public function membershipPartOneAction(){

		$session = new Zend_Session_Namespace('storage');
		$this->getHelper( 'layout' )->setLayout( 'welcome' );

		//POST
		//	post request, try to create user
		if( $this->_request->isPost() ){

			$form = new Application_Form_CreateUser();

			//VALID FORM
			//	form is valid... or is it...?
			if( $form->isValid($this->_request->getPost()) ){

				$userDAO = new Application_Model_User();

				//EMAIL IN USE
				//	check if the e-mail is in use
				if( $userDAO->fetchAll("email='{$form->getValue("email")}'")->count() > 0 ){
					$form->markAsError()
					->getElement("email")
					->addErrorMessage("Netfang upptekið")
					->markAsError();
					$this->view->form = $form;
					return ;
				}

				//PASSWORDS
				//	check if pass1 == pass2
				if ($form->getValue("pass1") != $form->getValue("pass2")){
					$form->markAsError()
					->getElement("pass2")
					->addErrorMessage("Lykilorðin stemma ekki")
					->markAsError();
					$this->view->form = $form;
					return ;
				}

				$session = new Zend_Session_Namespace('storage');
				$session->name = $form->getValue("name");
				$session->email = $form->getValue("email");
				$session->passwd = $form->getValue("pass1");
				$session->title = $form->getValue("title");

				$this->_redirect('/nyskra/2');
				return ;

				//				//INSERT
				//				//	create user in DB
				//				$userDAO->insert(array(
				//					'name' => $form->getValue("name"),
				//					'passwd' => new Zend_Db_Expr("MD5('{$form->getValue("pass1")}')"),
				//					'email' =>  $form->getValue("email"),
				//					'created_date' => new Zend_Db_Expr("NOW()"),
				//					'modified_date' => new Zend_Db_Expr("NOW()"),
				//					'title' => $form->getValue("title")
				//				));
				//
				//				//LOGIN
				//				//	try to login user
				//				//	and report to view
				//				$this->view->success = $this->_login(
				//					$form->getValue("email"),
				//					$form->getValue("pass1")
				//				);
				//
				//				if($this->view->success){
				//					$view = clone $this->view;
				//					$view->name = $form->getValue("name");
				//					$view->email = $form->getValue("email");
				//					$view->passwd = $form->getValue("pass1");
				//					$mail = new Ext_Mail("utf-8");
				//					$mail->setSubject("[Stjornvisi] Notendaaðgangur");
				//					$mail->addTo($form->getValue("email"));
				//					$mail->addBcc("stjornvisi@stjornvisi.is","Stjórnvísi");
				//					$mail->setBodyText(strip_tags($view->render("auth/_mail-newuser.phtml")));
				//					$mail->setBodyHtml($view->render("auth/_mail-newuser.phtml"));
				//					$mail->send();
				//				}
				//
				//				//INVALID FROM
				//				//	form is invalid
			}else{
				$this->view->form = $form;
			}
			//GET
			//	get request, return create-form
		}else{
			$this->view->form = new Application_Form_CreateUser(null,(object)array(
					'name'=> $session->name,
					'email'=> $session->email,
					'title'=> $session->title,
			));
		}


	}

	/**
	 *
	 * Enter description here ...
	 * @throws Zend_Controller_Action_Exception
	 */
	public function membershipPartTwoAction(){

		$session = new Zend_Session_Namespace('storage');

		if($this->_request->isPost()){

			//SELECT-COMPANY
			//	user has selected a company from
			//	list, we just store it in session and redirect
			//	to part 3 of process
			if( $this->_getParam('selectcompany',false) ){
				$session->company = (int)$this->_getParam('company');
				$this->_redirect('/nyskra/3');

				//SELECT-UNIVERSITY
				//	user has selected a university from
				//	list, we just store it in session and redirect
				//	to part 3 of process
			}elseif ($this->_getParam('selectuniversity',false)){
				$session->company = (int)$this->_getParam('university');
				$this->_redirect('/nyskra/3');
					
				//INDIVIDUAL-CREATE
				//	create company that is individual
			}elseif ($this->_getParam('createindividual',false)){
				$form = new Application_Form_CompanyIndividual();
				if($form->isValid($this->_request->getPost())){
					$session->company = (object)array(
							'name' => $form->getValue('name'),
							'ssn' => $form->getValue('ssn'),
							'address' => $form->getValue('address'),
							'zip' => $form->getValue('zip'),
							'businesstype' => 'Einstaklingur',
							'noofemployees' => null,
							'website' => null,
					);
					$this->_redirect('/nyskra/3');
				}else{
					$this->view->selected = 'createindividual';
					$this->view->university_select_form = new Application_Form_UniversitySelect();
					$this->view->company_select_form = new Application_Form_CompanySelect();
					$this->view->individual_form = $form;
					$this->view->company_form = new Application_Form_Company();
				}

				//COMPANY-CREATE
				//	create company that is company
			}elseif ($this->_getParam('createcompany',false)){
				$form = new Application_Form_Company();
				if($form->isValid($this->_request->getPost())){
					$companyDAO = new Application_Model_Company();
					if($companyDAO->fetchAll("name='{$form->getValue('name')}'")->count()){
						$form->markAsError()
						->getElement("name")
						->addErrorMessage("Nafn þegar á skrá")
						->markAsError();
						$this->view->selected = 'createindividual';
						$this->view->university_select_form = new Application_Form_UniversitySelect();
						$this->view->company_select_form = new Application_Form_CompanySelect();
						$this->view->individual_form = $form;
						$this->view->company_form = new Application_Form_Company();
					}else{
						$session->company = (object)array(
								'name' => $form->getValue('name'),
								'ssn' => $form->getValue('ssn'),
								'address' => $form->getValue('address'),
								'zip' => $form->getValue('zip'),
								'businesstype' => $form->getValue('businesstype'),
								'noofemployees' => $form->getValue('noofemployees'),
								'website' => $form->getValue('website'),
						);
						$this->_redirect('/nyskra/3');
					}
				}else{
					$this->view->selected = 'createcompany';
					$this->view->university_select_form = new Application_Form_UniversitySelect();
					$this->view->company_select_form = new Application_Form_CompanySelect();
					$this->view->individual_form = new Application_Form_CompanyIndividual();
					$this->view->company_form = $form;
				}

				//ERROR
				//	an unsupported option was selected
			}else{
				throw new Zend_Controller_Action_Exception("Action not supported",500);
			}
				
		}else{
				
			//SELECT FORM
			//	set which for is selected
			$this->view->selected = 'selectcompany';
				
			//UNIVERSITIES
			//	get all universities
			$this->view->university_select_form = new Application_Form_UniversitySelect();
				
			//COMPANIES
			//	get all companies that are not individuals
			//	or universities
			$this->view->company_select_form = new Application_Form_CompanySelect();
				
			//CREATE-INDIVIDUAL
			//	get form to create company-as-inidividual
			$this->view->individual_form = new Application_Form_CompanyIndividual(null,(object)array('name'=>$session->name));
				
			//CREATE-COMPANY
			//	get form to create company-as-company
			$this->view->company_form = new Application_Form_Company();
		}


		$this->getHelper( 'layout' )->setLayout( 'welcome' );
	}

	/**
	 *
	 * Enter description here ...
	 * @throws Zend_Controller_Action_Exception
	 */
	public function membershipPartThreeAction(){

		$session = new Zend_Session_Namespace('storage');

		$companyDAO = new Application_Model_Company();
		$userDAO = new Application_Model_User();
		$id = $userDAO->insert(array(
				'name' => $session->name,
				'email' => $session->email,
				'passwd' => new Zend_Db_Expr("MD5('".$session->passwd."')"),
				'title' => $session->title,
				'created_date' => new Zend_Db_Expr("NOW()"),
				'modified_date' => new Zend_Db_Expr("NOW()"),
				'frequency' => 1,
				'is_admin' => 0,
		));
		$company_id = null;


		if( isset($session->company) && is_numeric($session->company)  ){
			$userComapnyDAO = new Application_Model_CompanyHasUser();
			$userComapnyDAO->insert(array(
					'user_id' => $id,
					'company_id' => $session->company,
					'key_user' => 0
			));
			$company_id = $session->company;
		}elseif (isset($session->company) && is_object($session->company)){
			
			$company_id = $companyDAO->insert(array(
					'name' => $session->company->name,
					'ssn' => $session->company->ssn,
					'address' => $session->company->address,
					'zip' => $session->company->zip,
					'website' => $session->company->website,
					'number_of_employees' => $session->company->noofemployees,
					'business_type' => $session->company->businesstype,
			));

			$userComapnyDAO = new Application_Model_CompanyHasUser();
			$userComapnyDAO->insert(array(
					'user_id' => $id,
					'company_id' => $company_id,
					'key_user' => 1
			));

				
				
		}else{
			throw new Zend_Controller_Action_Exception("Action not supported",500);
		}

		Zend_Auth::getInstance()->getStorage()->write((object)array(
				'id' => $id,
				'name' => $session->name,
				'email' => $session->email,
				'title' => $session->title,
				'is_admin' => 0
		));
		$view = clone $this->view;
		$view->user = $userDAO->find($id)->current();
		$view->company = $companyDAO->find($company_id)->current();
		$view->new = !is_numeric($session->company);
		
		$mail = new Ext_Mail('utf-8');
		$mail->setSubject("Stjórnvísi : Skráning");
		$mail->addTo($view->user->email);
		$mail->addBcc("stjornvisi@stjornvisi.is","Stjórnvísi");
		$mail->addCc("stjornvisi@stjornvisi.is","Stjórnvísi");
		$mail->addBcc("hilmar@isproject.is","Hilmar Kári Hallbjörnsson");
		$mail->setBodyText( strip_tags($view->render("user/_user-register.phtml")));
		$mail->setBodyHtml( $view->render("user/_user-register.phtml") );
		$mail->send();

	}
}



