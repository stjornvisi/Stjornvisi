<?php
namespace Stjornvisi\Controller;

use ArrayObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Stjornvisi\Form\Company as CompanyForm;

class CompanyController extends AbstractActionController{

	/**
	 * Display one company entry.
	 *
	 * @return array|ViewModel
	 */
	public function indexAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $companyService = $sm->get('Stjornvisi\Service\Company');


        //COMPANY FOUND
        //
        if( ($company = $companyService->get( $this->params()->fromRoute('id', 0) )) != false ){
            $authService = new AuthenticationService();
            $access = $userService->getTypeByCompany(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $company->id
            );

            //ACCESS GRANTED
            //
            if( $access->is_admin || $access->type != null ){

                return new ViewModel(array(
                    'company' => $company,
                    'access' => $access
                ));
            //ACCESS DENIED
            }else{
                var_dump('403');
            }
        //COMPANY NOT FOUND
        //  404
        }else{
            var_dump('404');
        }

	}

	/**
	 * Get all companies that are not "einstaklingur".
	 *
	 * @return ViewModel
	 */
	public function listAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $companyService = $sm->get('Stjornvisi\Service\Company');

        $authService = new AuthenticationService();
        $access = $userService->getTypeByGroup(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
            null
        );

        return new ViewModel(array(
            'companies' => $companyService->fetchAll(array('einstaklingur')),
            'access' => $access
        ));

	}

	/**
	 *Set the role of employee at a company
	 *
	 * @return \Zend\Http\Response
	 */
	public function setRoleAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $companyService = $sm->get('Stjornvisi\Service\Company');


        //COMPANY FOUND
        //
        if( ($company = $companyService->get( $this->params()->fromRoute('id', 0) )) != false ){

            $authService = new AuthenticationService();
            $access = $userService->getTypeByCompany(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $company->id
            );

            //ACCESS GRANTED
            //  access granted
            if($access->is_admin || $access->type != null){
                $companyService->setEmployeeRole(
                    $company->id,
                    $this->params()->fromRoute('user', 0),
                    $this->params()->fromRoute('type', 0)
                );
                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
            //ACCESS DENIED
            //  access denied
            }else{
                var_dump('403');
            }

        //COMPANY NOT FOUND
        //  404
        }else{
            var_dump('404');
        }
    }

	/**
	 * Create company.
	 *
	 * @return \Zend\Http\Response|ViewModel
	 */
	public function createAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $companyService = $sm->get('Stjornvisi\Service\Company');
        $valueService = $sm->get('Stjornvisi\Service\Values');

        $authService = new AuthenticationService();
        $access = $userService->getTypeByCompany(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
            null
        );

        //ACCESS GRANTED
        //
        if( $authService->hasIdentity() ){

            $form = new CompanyForm( $valueService );

            $form->setAttribute('action', $this->url()->fromRoute('fyrirtaeki/create'));
            //POST
            //  http post request
            if( $this->request->isPost() ){
                $form->setData( $this->request->getPost() );
                if( $form->isValid() ){
                    $data = $form->getData();
                    unset($data['submit']);
                    $id = $companyService->create($data);
                    $companyService->addUser($id, $authService->getIdentity()->id,1);
                    return $this->redirect()->toRoute('fyrirtaeki/index',array('id'=>$id));
                }else{
                    return new ViewModel(array(
                        'access' => $access,
                        'form' => $form
                    ));
                }
                //QUERY
                //  http get request
            }else{
                return new ViewModel(array(
                    'access' => $access,
                    'form' => $form
                ));
            }

            //ACCESS DENIED
        }else{
            var_dump('403');
        }

        /*
		$auth = Zend_Auth::getInstance()->getIdentity();
		//POST
		//	post request
		if( $this->_request->isPost() ){

			$form = new Application_Form_Company();

			//VALID
			//	form is valid
			if($form->isValid($this->_request->getPost())){
				//TODO use Ext_Filter_Urlsave
				$companyUrl = new Ext_Filter_Urlsafe($form->getValue('name'));
				$companyDAO = new Application_Model_Company();
				$companyhasuserDAO = new Application_Model_CompanyHasUser();

				try{
					$id = $companyDAO->insert(array(
                		'name' => $form->getValue('name'),
                		'ssn' => $form->getValue('ssn'),
                		'address' => $form->getValue('address'),
                		'zip' => $form->getValue('zip'),
                		'business_type' => $form->getValue('businesstype'),
                		'number_of_employees'	=> $form->getValue('noofemployees'),
                		'website' => $form->getValue('website'),
                		'safe_name' => $companyUrl->__toString()
					));

					$companyhasuserDAO->insert(array(
        				'company_id'	=> $id,
        				'user_id'		=> $auth->id,
						'key_user'		=> true
					));
						
						
					//MAIL
					//	compose, construct and send mail
					$view = clone $this->view;
					$view->user = Zend_Auth::getInstance()->getIdentity();
					$view->company = $companyDAO->find($id)->current();
						
					$mail = new Ext_Mail('utf-8');
					$mail->setSubject("[Stjórnvísi : tilkynning : Stofnun fyrirtækis");
					$mail->addTo(
					Zend_Auth::getInstance()->getIdentity()->email,
					Zend_Auth::getInstance()->getIdentity()->name
					);
					$mail->addCc('stjornvisi@stjornvisi.is', "Stjórnvísi");
					$mail->addBcc("stjornvisi@stjornvisi.is", "Stjórnvísi");
					$mail->setBodyText( strip_tags($view->render("company/_mail-register.phtml") ));
					$mail->setBodyHtml( $view->render("company/_mail-register.phtml") );

						
					$mail->send();
						
				}catch( Zend_Db_Exception $e ){
					/*
					 * If the error code 1062 exists in getMessage(), then gracefully
					 * let the user know that the company already exists in the database.
					 *
					 *
					$msg = $e->getMessage();

					if (strlen(strstr($msg,'1062'))>0){
						$this->_redirect("/company/error");
						//TODO wouldn't it be a better idea to just display the create
						//	form with the 'company-name' box in red (error)?
					}else{
						throw $e;
					}
				}

				$this->_redirect("/fyrirtaeki/{$companyUrl->__toString()}");
				//INVALID
				//	form is invalid
			}else{
				$this->view->form = $form;
			}

			//GET
			//	get request
		}else{
				
			if( $this->_getParam('type',null)=='individual' ){
				$this->view->form = new Application_Form_Company(null,(object)array(
					'name' => Zend_Auth::getInstance()->getIdentity()->name,
					'individual' => true
				));
			}else{
				$this->view->form = new Application_Form_Company();
			}

		}
        */
	}

	/**
	 * Update company.
	 *
	 * @return \Zend\Http\Response|ViewModel
	 */
	public function updateAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $companyService = $sm->get('Stjornvisi\Service\Company');
        $valueService = $sm->get('Stjornvisi\Service\Values');

        //COMPANY FOUND
        //
        if( ($company = $companyService->get( $this->params()->fromRoute('id', 0) )) != false ){
            $authService = new AuthenticationService();
            $access = $userService->getTypeByCompany(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $company->id
            );

            //ACCESS GRANTED
            //
            if( $access->is_admin || $access->type != null ){

                $form = new CompanyForm(
                    $valueService->getBusinessTypes(),
                    $valueService->getPostalCode(),
                    $valueService->getCompanySizes()
                );
                $form->setAttribute('action', $this->url()->fromRoute('fyrirtaeki/update',array('id'=>$company->id)));
                //POST
                //  http post request
                if( $this->request->isPost() ){
                    $form->setData( $this->request->getPost() );
                    if( $form->isValid() ){
                        $data = $form->getData();
                        unset($data['submit']);
                        $companyService->update($company->id, $data);
                        return $this->redirect()->toRoute('fyrirtaeki/index',array('id'=>$company->id));
                    }else{
                        return new ViewModel(array(
                            'company' => $company,
                            'access' => $access,
                            'form' => $form
                        ));
                    }
                //QUERY
                //  http get request
                }else{
                    $form->bind( new ArrayObject($company) );
                    return new ViewModel(array(
                        'company' => $company,
                        'access' => $access,
                        'form' => $form
                    ));
                }

            //ACCESS DENIED
            }else{
                var_dump('403');
            }
        //COMPANY NOT FOUND
        //  404
        }else{
            var_dump('404');
        }

	    /*
		$companyDAO = new Application_Model_Company();
		
		//RESOURCE FOUND
		//	found the resource
		if( ( $company=$companyDAO->find($this->_getParam('id'))->current() )!= null ){
			
			//ACCESS GRANTED
			//	user has access to this resource
			if( $this->_helper->acl()->validate( new Ext_Acl_Company($company->id), Ext_Acl_Company::RULE_MANAGE ) ){
				
				//POST
				//	http post request
				if( $this->_request->isPost() ){
					
					$form = new Application_Form_Company();
					//VALID
					//	form is valid
					if($form->isValid($this->_request->getPost())){
						$companyDAO->update(array(
							'name' => $form->getValue('name'),
						    'ssn' => $form->getValue('ssn'),
		                	'address' => $form->getValue('address'),
		                	'zip' => $form->getValue('zip'),
		                	'business_type' => $form->getValue('businesstype'),
		                	'number_of_employees'	=> $form->getValue('noofemployees'),
		                	'website' => $form->getValue('website'),
						), "id = {$this->_getParam('id')}");
						
						$this->_redirect("/fyrirtaeki/{$company->id}");
						
					//INVALID
					//	form is invalid
					}else{
						$this->view->form = $form;
					}
					
				//GET
				//	http query request
				}else{
					$this->view->form = new Application_Form_Company('update', $company, null);
				}
				
			//ACCESS DENIED
			//	user is not allowed 
			}else{
				throw new Zend_Controller_Action_Exception("Access Denied",401);
			}
			
		//RESOURCE NOT FOUND
		//	the resource does not exist
		}else{
			throw new Zend_Controller_Action_Exception("Resource Not Found",404);
		}
        */
	}

	/**
	 * Delete company.
	 *
	 * @return \Zend\Http\Response
	 */
	public function deleteAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $companyService = $sm->get('Stjornvisi\Service\Company');

        //COMPANY FOUND
        //
        if( ($company = $companyService->get( $this->params()->fromRoute('id', 0) )) != false ){
            $authService = new AuthenticationService();
            $access = $userService->getTypeByCompany(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $company->id
            );

            //ACCESS GRANTED
            //
            if( $access->is_admin || $access->type == 1 ){
                $companyService->delete( $company->id );
				return $this->redirect()->toRoute('fyrirtaeki');
            //ACCESS DENIED
            //
            }else{
                var_dump('403');
            }

        //COMPANY NOT FOUND
        //  404
        }else{
            var_dump('404');
        }
	}
	
	/**
	 * Remove user from list of employees
	 * @throws Zend_Controller_Action_Exception
	 */
	public function disconnectUserAction(){
		
		if( $this->_helper->acl->validate( new Ext_Acl_Company($this->_getParam('company-id')), Ext_Acl_Company::RULE_MANAGE ) ){
			$companyUserDAO = new Application_Model_CompanyHasUser();
			$companyUserDAO->delete("user_id={$this->_getParam('user-id')} AND company_id={$this->_getParam('company-id')}");
			$this->_redirect("fyrirtaeki/{$this->_getParam('company-id')}#starfsfolk");
		}else{
			throw new Zend_Controller_Action_Exception('Access Denied',401);
		}
	}
	
	/**
	 * Connect user to a company.
	 *
	 * @throws Zend_Controller_Action_Exception
	 * @deprecated
	 */
	public function connectUserAction(){
		//LOGGED IN
		//	user is logged in
		if( Zend_Auth::getInstance()->hasIdentity() ){
				
			$this->view->university = ($this->_getParam('type')=='university')
			? true
			: false ;
				
			//POST
			//	request is post, create entry
			if( $this->_request->isPost() ){
				$companyDAO = new Application_Model_Company();

				//CONNECTION TYPE
				//	is this an individual connecting to a company
				//	or an individual connecting to a university.
				if( $this->_getParam('type')=='university' ){
					$form = new Application_Form_UserCompany(
					null,$companyDAO->fetchAll( "business_type = 'Háskóli'", 'name ASC' ));
				}else{
					$form = new Application_Form_UserCompany(
					null,$companyDAO->fetchAll( "business_type != 'einstaklingur'", 'name ASC' ));
				}
					
				//VALID FORM
				//	form is valid
				if( $form->isValid($this->_request->getPost()) ){
					$companyHasUserDAO = new Application_Model_CompanyHasUser();
					//DELETE
					//	delete old connections
					$companyHasUserDAO->delete("user_id=".Zend_Auth::getInstance()->getIdentity()->id);
					//INSERT
					//	insert new connection
					$companyHasUserDAO->insert( array(
						"user_id"		=> Zend_Auth::getInstance()->getIdentity()->id,
						"company_id"	=> $form->getValue( 'company' ),
						"key_user"		=> false
					));
						
					//Add the company to the session
					$authobj = Zend_Auth::getInstance()->getStorage()->read();
					$authobj->company_id = $form->getValue( 'company' );
						
					//Redirect the user to his entry page
					$this->_redirect("/notandi");
						
					//INVALID FORM
					//	the form is invalid
				}else{
					$this->view->form = $form;
				}

				//GET
				//	query request
			}else{
				$companyDAO = new Application_Model_Company();
				//CONNECTION TYPE
				//	is this an individual connecting to a company
				//	or an individual connecting to a university.
				if( $this->_getParam('type')=='university' ){
					$this->view->form = new Application_Form_UserCompany(
					null,$companyDAO->fetchAll( "business_type = 'Háskóli'", 'name ASC' ));
				}else{
					$this->view->form = new Application_Form_UserCompany(
					null,$companyDAO->fetchAll( "business_type != 'einstaklingur'", 'name ASC' ));
				}

			}
				
			//NOT LOGGED IN
			//	user is not logged in, he will get 401
		}else{
			throw new Zend_Controller_Action_Exception("Access Denied",401);
		}

	}
}
