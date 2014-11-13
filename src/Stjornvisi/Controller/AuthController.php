<?php

namespace Stjornvisi\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;

use Zend\Session\Container;

use Stjornvisi\Form\LostPassword as LostPasswordForm;
use Stjornvisi\Notify\Password as PasswordNotify;

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequestException;
use Facebook\FacebookRequest;
use Facebook\GraphUser;


use OAuth\OAuth2\Service\Linkedin;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

class AuthController extends AbstractActionController{

	const LOGIN_CALLBACK_FACEBOOK = '/innskra/callback-login-facebook';

	/**
	 * Create user.
	 *
	 * @todo send email
	 *
	 */
	public function createUserAction(){

		$session = new Container('create_user');
		$sm = $this->getServiceLocator();
		$form = $sm->get('Stjornvisi\Form\NewUserCredentials');
		$form->setAttribute('action',$this->url()->fromRoute('notandi/create'));

		//POST
		if($this->request->isPost() ){
			$form->setData($this->request->getPost());
			if( $form->isValid() ){
				//$data = (array)$form->getData();
				$session->name = $form->get('name')->getValue();
				$session->email = $form->get('email')->getValue();
				$session->title = $form->get('title')->getValue();

				return $this->redirect()->toRoute('notandi/company');

			}else{
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

	public function createUserCompanyAction(){

		$sm = $this->getServiceLocator();

		$companyService = $sm->get('Stjornvisi\Service\Company');
		/** @var  $companyService \Stjornvisi\Service\Company */

		$companyForm = $sm->get('Stjornvisi\Form\NewUserCompany');
		$companySelectForm =  $sm->get('Stjornvisi\Form\NewUserCompanySelect');
		$individualForm = $sm->get('Stjornvisi\Form\NewUserIndividual');
		$universitySelectForm =  $sm->get('Stjornvisi\Form\NewUserUniversitySelect');

		//POST
		//	post request

		if($this->request->isPost() ){
			$post = $this->request->getPost();

			//CREATE NEW COMPANY
			//	create a new company.
			if( isset($post['submit-company-create']) ){
				$companyForm->setData($this->request->getPost());
				if( $companyForm->isValid() ){

					$data = (array)$companyForm->getData();
					$id = $companyService->create(array(
						'name' => $data['company-name'],
						'ssn' => $data['company-ssn'],
						'address' => $data['company-address'],
						'zip' => $data['company-zip'],
						'website' => $data['company-web'],
						'number_of_employees' => $data['company-size'],
						'business_type' => $data['company-type']
					));

					$session = new Container('create_user');
					$session->company = $id;

					return $this->redirect()->toRoute('notandi/login');
				}else{
					return new ViewModel(array(
						'companyForm' => $companyForm,
						'companySelectForm' => $companySelectForm,
						'individualForm' => $individualForm,
						'universitySelectForm' => $universitySelectForm,
					));
				}
			//SELECT COMPANY
			//	company exists, user selects
			}elseif( isset($post['submit-company-select']) ){
				$companySelectForm->setData($this->request->getPost());
				if( $companySelectForm->isValid() ){
					$data = (array)$companySelectForm->getData();
					$session = new Container('create_user');
					$session->company = $data['company-select'];
					return $this->redirect()->toRoute('notandi/login');
				}else{
					return new ViewModel(array(
						'companyForm' => $companyForm,
						'companySelectForm' => $companySelectForm,
						'individualForm' => $individualForm,
						'universitySelectForm' => $universitySelectForm,
					));
				}
			//CREATE INDIVIDUAL
			//	create a company that is only for this user.
			}elseif( isset($post['submit-individual']) ){
				$session = new Container('create_user');
				$individualForm->setData($this->request->getPost());
				if( $individualForm->isValid() ){
					$data = (array)$individualForm->getData();
					$id = $companyService->create(array(
						'name' => $session->name,
						'ssn' => $data['person-ssn'],
						'address' => $data['person-address'],
						'zip' => $data['person-zip'],
						'website' => null,
						'number_of_employees' => 'Einstaklingur',
						'business_type' => 'Einstaklingur'
					));

					$session->company = $id;

					return $this->redirect()->toRoute('notandi/login');
				}else{
					return new ViewModel(array(
						'companyForm' => $companyForm,
						'companySelectForm' => $companySelectForm,
						'individualForm' => $individualForm,
						'universitySelectForm' => $universitySelectForm,
					));
				}
			//SELECT UNIVERSITY
			//	user is selecting university
			}elseif( isset($post['submit-university-select']) ){
				if( $universitySelectForm->isValid() ){

				}else{
					return new ViewModel(array(
						'companyForm' => $companyForm,
						'companySelectForm' => $companySelectForm,
						'individualForm' => $individualForm,
						'universitySelectForm' => $universitySelectForm,
					));
				}
			}else{
				//error
			}

		//QUERY
		//	get request
		}else{
			return new ViewModel(array(
				'companyForm' => $companyForm,
				'companySelectForm' => $companySelectForm,
				'individualForm' => $individualForm,
				'universitySelectForm' => $universitySelectForm,
			));
		}

	}

	public function createUserLoginAction(){
		$session = new Container('create_user');

		//TODO create user;

		return new ViewModel(array(
			'data' => $session
		));
	}
	/**
	 * Login user
	 * @todo count frequency
	 *
	 */
	public function loginAction(){

		/*
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
                        'form' => $form,
						'facebook' => $this->getServiceLocator()->get('Facebook')
                    ));
                }

            //QUERY
            //  http get request, user gets login form
            }else{
                return new ViewModel(array(
                    'form' => new Login(),
					'facebook' => $this->getServiceLocator()->get('Facebook')
                ));
            }
        }
		*/
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

	public function callbackLoginLinkedinAction(){
		$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
		$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
		$currentUri->setQuery('');

		$config = $this->getServiceLocator()->get('Config');
		// Session storage
		$storage = new Session();

		// Setup the credentials for the requests
		$credentials = new Credentials(
			$config['linkedin']['appId'],
			$config['linkedin']['secret'],
			$currentUri->getAbsoluteUri()
		);

		$serviceFactory = new \OAuth\ServiceFactory();

		// Instantiate the Linkedin service using the credentials, http client and storage mechanism for the token
		/** @var $linkedinService Linkedin */
		$linkedinService = $serviceFactory->createService('linkedin', $credentials, $storage, array('r_basicprofile'));
		if (!empty($_GET['code'])) {
			// retrieve the CSRF state parameter
			$state = isset($_GET['state']) ? $_GET['state'] : null;
			// This was a callback request from linkedin, get the token
			$token = $linkedinService->requestAccessToken($_GET['code'], $state);
			// Send a request with it. Please note that XML is the default format.
			$result = json_decode($linkedinService->request('/people/~?format=json'), true);
			// Show some of the resultant data
			echo 'Your linkedin first name is ' . $result['firstName'] . ' and your last name is ' . $result['lastName'];
		} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
			$url = $linkedinService->getAuthorizationUri();
			header('Location: ' . $url);
			exit();
		} else {
			$url = $currentUri->getRelativeUri() . '?go=go';
			echo "<a href='$url'>Login with Linkedin!</a>";
		}

	}

	/**
	 * Callback from Facebook.
	 *
	 * When user goes through the Facebook oAuth 2.0 process
	 * of login in, after he has logged into Facebook, he/she is redirected to this
	 * this action. Here he/she is authenticated to the system and if that works the user
	 * is logged in. If this does not work, the user is asked it this is his first time logging
	 * in via Facebook and if he/she is sure that he/she has an account.
	 *
	 * @return \Zend\Http\Response
	 * @todo count frequency
	 */
	public function callbackLoginFacebookAction(){

		//GET SERVER
		//	 this check has to be done for instances where this
		//	is not run as an web-application
		$server = isset( $_SERVER['HTTP_HOST'] )
			? "http://".$_SERVER['HTTP_HOST']
			: 'http://0.0.0.0' ;

		//FACEBOOK CONFIG
		//	get config and use it to cnfigure facebook session
		//	and login functionality
		$config = $this->getServiceLocator()->get('Config');
		FacebookSession::setDefaultApplication(
			$config['facebook']['appId'],
			$config['facebook']['secret']
		);//TODO should this be in a global space


		//ERROR
		$error = $this->params()->fromQuery('error');
		if( $error == 'access_denied' ){
			return new ViewModel(array(
				'error' => 'access_denied'
			));
		}


		//KEY
		//	check if there is a query parameter called $key along
		//	for the ride. If so; then the user is trying to connect old account
		//	to Facebook.
		$key = $this->params()->fromQuery('key'); //TODO validate this key

		//CONNECTING OLD ACCOUNT
		//	if $key is present, then the callback from Facebook will contain it and
		//	we have to reflect it in the callback validation
		$helper = new FacebookRedirectLoginHelper(
			($key)
				? $server.AuthController::LOGIN_CALLBACK_FACEBOOK.'?key='.$key
				: $server.AuthController::LOGIN_CALLBACK_FACEBOOK
		);

		//LOGIN
		//	try to log in user
		try {
			//FACEBOOK OBJECT
			//	get user object/properties from facebook graph
			$session = $helper->getSessionFromRedirect();

			$me = (new FacebookRequest(
				$session, 'GET', '/me'
			))->execute()->getGraphObject(GraphUser::className())->asArray();


			//CONNECT OLD ACCOUNT CUT-IN
			//	if $key is set, then the user is trying to connect old account to his
			//	Facebook. What we do here is to find the user based on the hash that we got
			//	back from facebook, then we inject the Facebook Auth-ID into his table just
			//	in time so that '$auth = new AuthenticationService();' line of code will pick
			//	it up and authenticate the user. This is just a little detour to quickly connect
			//	the user to a facebook account just before we authenticate him.
			if($key){
				$sm = $this->getServiceLocator();
				$userService = $sm->get('Stjornvisi\Service\User');
				/** @var $userService \Stjornvisi\Service\User */
				if( ($user = $userService->getByHash( $key )) != null ){
					$userService->setOauth( $user->id, $me['id'], 'facebook' );
				//USER NOT FOUND
				//	can't find the user based on hash
				}else{
					return new ViewModel(array(
						'error' => 'user_undefined'
					));
				}
			}

			//AUTHENTICATE
			//	try to authenticate user against user database
			$auth = new AuthenticationService();
			$sm = $this->getServiceLocator();
			$authAdapter =  $sm->get('Stjornvisi\Auth\Facebook');
			$authAdapter->setKey( $me['id'] );
			$result = $auth->authenticate($authAdapter);

			//VALID
			//	user has logged in before via Facebook
			if( $result->isValid() ){
				$sessionManager = new SessionManager();
				$sessionManager->rememberMe(21600000); //250 days
				return $this->redirect()->toRoute('home');
			//INVALID
			//	user hasn't logged in with facebook before. We have
			//	to initialize the connection process.
			}else{
				return new ViewModel(array(
					'error' => 'user_disconnected'
				));
			}

		//CAN'T LOGIN USER
		//	Facebook login library issues exception.
		//	Facebook returns an error
		} catch(FacebookRequestException $ex) {
			// When Facebook returns an error
			return new ViewModel(array(
				'error' => $ex->getMessage()
			));
		//ERROR
		//	There was a more generic error
		//	When validation fails or other local issues
		} catch(\Exception $ex) {
			return new ViewModel(array(
				'error' => $ex->getMessage()
			));
		}

    }

	/**
	 * Connect old account to Facebook.
	 *
	 * User has an old Stjornvisi account and wants to connect his/her
	 * Facebook account to it.
	 *
	 * There can only be POST request to this actions, since the user is always
	 * providing an old e-mail address via HTMLForm.
	 *
	 * @return ViewModel
	 */
	public function requestConnectionFacebookAction(){

		//GET SERVER
		//	 this check has to be done for instances where this
		//	is not run as an web-application
		$server = isset( $_SERVER['HTTP_HOST'] )
			? "http://".$_SERVER['HTTP_HOST']
			: 'http://0.0.0.0' ;

		if( $this->request->isPost() ){

			$post = $this->request->getPost()->getArrayCopy();
			$sm = $this->getServiceLocator();
			$userService = $sm->get('Stjornvisi\Service\User'); /** @var  $userService \Stjornvisi\Service\User */

			$user = $userService->get( $post['email'] );
			if( $user ){

				//FACEBOOK CONFIG
				//	get config and use it to configure facebook session
				//	and login functionality
				$config = $this->getServiceLocator()->get('Config');
				FacebookSession::setDefaultApplication(
					$config['facebook']['appId'],
					$config['facebook']['secret']
				);//TODO should this be in a global space
				$helper = new FacebookRedirectLoginHelper(
					$server. AuthController::LOGIN_CALLBACK_FACEBOOK . '?key='.$user->hash
				);

				return new ViewModel(array(
					'user' => $user,
					'url' => $server,
					'facebook' => $helper->getLoginUrl()
				));


			//USER NOT FOUND
			}else{
				return new ViewModel(array(
					'user' => null
				));
			}


		}else{

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
						'action' => PasswordNotify::REGENERATE,
						'data' => (object)array(
								'user_id' => $user->id,
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
