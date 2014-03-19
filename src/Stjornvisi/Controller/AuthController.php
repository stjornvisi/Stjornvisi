<?php

namespace Stjornvisi\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use \Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Stjornvisi\Form\Login;
use Stjornvisi\Form\LostPassword as LostPasswordForm;


class AuthController extends AbstractActionController{

	/**
	 * Create user
	 * @todo send email
	 * @todo there must be a better way for the
	 * 	comapny dropdown that to go to the DB
	 * 	every time.
	 *
	 */
	public function createUserAction(){ 

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

				//INSERT
				//	create user in DB
				$userDAO->insert(array(
					'name' => $form->getValue("name"),
					'passwd' => new Zend_Db_Expr("MD5('{$form->getValue("pass1")}')"),
					'email' =>  $form->getValue("email"),
					'created_date' => new Zend_Db_Expr("NOW()"),
					'modified_date' => new Zend_Db_Expr("NOW()"),
					'title' => $form->getValue("title")
				));

				//LOGIN
				//	try to login user
				//	and report to view
				$this->view->success = $this->_login(
					$form->getValue("email"),
					$form->getValue("pass1")
				);

				if($this->view->success){
					$view = clone $this->view;
					$view->name = $form->getValue("name");
					$view->email = $form->getValue("email");
					$view->passwd = $form->getValue("pass1");
					$mail = new Ext_Mail("utf-8");
					$mail->setSubject("[Stjornvisi] Notendaaðgangur");
					$mail->addTo($form->getValue("email"));
					$mail->addBcc("stjornvisi@stjornvisi.is","Stjórnvísi");
					$mail->setBodyText(strip_tags($view->render("auth/_mail-newuser.phtml")));
					$mail->setBodyHtml($view->render("auth/_mail-newuser.phtml"));
					$mail->send();
				}
				//INVALID FROM
				//	form is invalid
			}else{
				$this->view->form = $form;
			}
			//GET
			//	get request, return create-form
		}else{
			$companyDAO = new Application_Model_Company();
			$this->view->form = new Application_Form_CreateUser(
				$companyDAO->fetchAll(null,'name')
			);
		}

	}

	/**
	 * Login user
	 * @todo count frequency
	 *
	 */
	public function loginAction(){

        $auth = new AuthenticationService();

        //IS LOGGED IN
        //  user is logged in
        if( $auth->hasIdentity() ){

        //NOT LOGGED IN
        //  user is not logged in
        }else{

            //POST
            //  http post request, trying to log in
            if( $this->request->isPost() ){

                $form = new Login();
                $form->setData($this->request->getPost() );
                //VALID
                //  valid login form
                if( $form->isValid() ){
                    //AUTH
                    //  get auth adapter, sen it the credentials,
                    //  authenticate, through the adapter and
                    //  take appropriate steps.
                    $data = $form->getData();
                    $sm = $this->getServiceLocator();
                    $authAdapter =  $sm->get('Stjornvisi\Auth\Adapter');
                    $authAdapter->setCredentials($data['email'],$data['passwd']);
                    $result = $auth->authenticate($authAdapter);
                    if( $result->isValid() ){
                        $sessionManager = new SessionManager();
                        $sessionManager->rememberMe(21600000); //250 days
                        return $this->redirect()->toRoute('home');
                    }else{
                        return new ViewModel(array(
                            'form' => $form
                        ));
                    }
                //INVALID
                //  invalid login form
                }else{
                    return new ViewModel(array(
                        'form' => $form
                    ));
                }

            //QUERY
            //  http get request, user gets login form
            }else{
                return new ViewModel(array(
                    'form' => new Login()
                ));
            }
        }
	}

	/**
	 * Logout and destroy session
	 *
	 */
	public function logoutAction(){
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        return $this->redirect()->toRoute('home');
	}

	/**
	 * Callback from Facebook.
	 *
	 * @return \Zend\Http\Response
	 * @todo count frequency
	 */
	public function callbackAction(){

        $auth = new AuthenticationService();


        if( isset($_GET['code']) ){
            $token =  file_get_contents("https://graph.facebook.com/oauth/access_token?client_id=293461407395158&redirect_uri=http://localhost:8080/callback&client_secret=ba09275f17ecac9608045c249aa9b609&code={$_GET['code']}");
            $arr = array();
            preg_match("/access_token=([^&]+)(?:&expires=(.*))?/", $token,$arr);
            $access_token = $arr[1];

            $result = file_get_contents("https://graph.facebook.com/me?access_token={$access_token}");
            $resultObject = json_decode($result);

            $sm = $this->getServiceLocator();
            $authAdapter =  $sm->get('Stjornvisi\Auth\Facebook');
            $authAdapter->setKey( $resultObject->id );
            $result = $auth->authenticate($authAdapter);
            if( $result->isValid() ){
                $sessionManager = new SessionManager();
                $sessionManager->rememberMe(21600000); //250 days
            }
            return $this->redirect()->toRoute('home');
        }
    }

	/**
	 * Request new password.
	 *
	 * @return ViewModel
	 */
	public function lostPasswordAction(){

		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$form = new LostPasswordForm();
		$form->setAttribute('action',$this->url()->fromRoute('notandi/lost-password'));
		if( $this->request->isPost() ){
			$form->setData( $this->request->getPost() );
			if( $form->isValid() ){
				$user = $userService->get( $form->get('email')->getValue() );
				if($user){
					$password = $this->_createPassword(20);
					$userService->setPassword( $user->id, $password );
					$this->getEventManager()->trigger('notify',$this,array(
						'action' => 'auth.lost-password',
						'recipients' => array($user),
						'priority' => true,
						'data' => (object)array(
								'user' => $user,
								'password' => $password,
							),
					));
					return new ViewModel(array(
						'message' => 'Nýtt lykilorð hefur verið sent',
						'form' => $form
					));
				}else{
					$form->get('email')->setMessages(array('Notandi fannst ekki'));
					return new ViewModel(array(
						'form' => $form,
						'message' => null
					));
				}
			}else{
				return new ViewModel(array(
					'form' => $form,
					'message' => null
				));
			}
		}else{
			return new ViewModel(array(
				'form' => $form,
				'message' => null
			));
		}
	}

	/**
	 * Create a random password
	 * @param int $length
	 * @return string
	 */
	private function _createPassword($length) {
		$chars = "234567890abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$i = 0;
		$password = "";
		while ($i <= $length) {
			$password .= $chars{mt_rand(0,strlen($chars))};
			$i++;
		}
		return $password;
	}

}
