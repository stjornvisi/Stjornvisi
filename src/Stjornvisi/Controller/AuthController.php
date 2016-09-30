<?php

namespace Stjornvisi\Controller;

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use Facebook\GraphUser;
use Stjornvisi\Auth\Adapter as AuthAdapter;
use Stjornvisi\Auth\Facebook as AuthFacebook;
use Stjornvisi\Form\Login;
use Stjornvisi\Form\LostPassword as LostPasswordForm;
use Stjornvisi\Form\NewUserCompany as FormNewUserCompany;
use Stjornvisi\Form\NewUserCompanySelect as FormNewUserCompanySelect;
use Stjornvisi\Form\NewUserCredentials as FormNewUserCredentials;
use Stjornvisi\Form\NewUserIndividual as FormNewUserIndividual;
use Stjornvisi\Form\NewUserPassword;
use Stjornvisi\Form\NewUserUniversitySelect as FormNewUserUniversitySelect;
use Stjornvisi\Module;
use Stjornvisi\Notify\Password as PasswordNotify;
use Stjornvisi\Service\Company as CompanyService;
use Stjornvisi\Service\User as UserService;
use Stjornvisi\Service\Values;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;


/**
 * Login / Logout. Create Users and connect the via oAuth etc...
 *
 * Class AuthController
 *
 * @package Stjornvisi\Controller
 * @property HttpRequest $request
 * @property HttpResponse $response
 * @method HttpRequest getRequest()
 * @method HttpResponse getResponse()
 */
class AuthController extends AbstractActionController
{
    const AUTH_COOKIE = 'backpfeifengesicht';

    /**
     * @var string Facebook callback URL
     */
    const LOGIN_CALLBACK_FACEBOOK = '/innskra/callback-login-facebook';

    /**
     * Create user.
     *
     * First installment of creating new user in the system.
     *
     * If POST, all information is collected and stored in a session,
     * nothing is written to the database....
     */
    public function createUserAction()
    {
        $session = $this->createSessionContainer();

        $sm = $this->getServiceLocator();

        $form = $sm->get(FormNewUserCredentials::class);
        $form->setAttribute('action', $this->url()->fromRoute('access/create'));

        //POST
        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());

            //VALIDATE FORM
            //	validate the form and if valid, store the values in session
            //	and move on to the next part of the creation process.
            if ($form->isValid()) {
                //$data = (array)$form->getData();
                $session->name = $form->get('name')->getValue();
                $session->email = $form->get('email')->getValue();
                $session->title = $form->get('title')->getValue();

                return $this->redirect()->toRoute('access/company');
            } else {
                return new ViewModel(['form' => $form]);
            }
            //QUERY
        } else {
            return new ViewModel(['form' => $form]);
        }
    }

    /**
     * Create company.
     *
     * Next installment of creating new user in the system.
     * This time it's the company.
     *
     * @return \Zend\Http\Response|ViewModel|array
     */
    public function createUserCompanyAction()
    {
        $sm = $this->getServiceLocator();

        $companyService = $sm->get(CompanyService::class);

        //FORMS
        //	create and configure all needed forms.
        $companyForm = $sm->get(FormNewUserCompany::class);
        $companyForm->setAttribute('action', $this->url()->fromRoute('access/company'));

        $companySelectForm =  $sm->get(FormNewUserCompanySelect::class);
        $companySelectForm->setAttribute('action', $this->url()->fromRoute('access/company'));

        $individualForm = $sm->get(FormNewUserIndividual::class);
        $individualForm->setAttribute('action', $this->url()->fromRoute('access/company'));

        $universitySelectForm =  $sm->get(FormNewUserUniversitySelect::class);
        $universitySelectForm->setAttribute('action', $this->url()->fromRoute('access/company'));

        $session = $this->createSessionContainer();
        $companySelectForm->detectFromEmail($session->email);

        //POST
        //	post request
        if ($this->request->isPost()) {
            $post = $this->request->getPost();

            //CREATE NEW COMPANY
            //	create a new company.
            if (isset($post['submit-company-create'])) {
                $companyForm->setData($this->request->getPost());
                if ($companyForm->isValid()) {
                    $data = (array)$companyForm->getData();
                    $id = $companyService->create(
                        [
                        'name' => $data['company-name'],
                        'ssn' => $data['company-ssn'],
                        'address' => $data['company-address'],
                        'zip' => $data['company-zip'],
                        'website' => $data['company-web'],
                        'number_of_employees' => $data['company-size'],
                        'business_type' => $data['company-type']
                        ]
                    );

                    $session->company = $id;
                    $session->company_key = 1;

                    return $this->redirect()->toRoute('access/login');
                }
                else {
                    return new ViewModel(
                        [
                        'companyForm' => $companyForm,
                        'companySelectForm' => $companySelectForm,
                        'individualForm' => $individualForm,
                        'universitySelectForm' => $universitySelectForm,
                        'panel' => 1,
                        ]
                    );
                }
            //SELECT COMPANY
            //	company exists, user selects
            }
            elseif (isset($post['submit-company-select'])) {
                $companySelectForm->setData($this->request->getPost());

                if ($companySelectForm->isValid()) {
                    $data = (array)$companySelectForm->getData();
                    $session = $this->createSessionContainer();
                    $session->company = $data['company-select'];
                    $session->company_key = 0;
                    return $this->redirect()->toRoute('access/login');
                }
                else {
                    return new ViewModel(
                        [
                        'companyForm' => $companyForm,
                        'companySelectForm' => $companySelectForm,
                        'individualForm' => $individualForm,
                        'universitySelectForm' => $universitySelectForm,
                        'panel' => 1,
                        ]
                    );
                }
            //CREATE INDIVIDUAL
            //	create a company that is only for this user.
            }
            elseif (isset($post['submit-individual'])) {
                $session = $this->createSessionContainer();
                $individualForm->setData($this->request->getPost());
                if ($individualForm->isValid()) {
                    $data = (array)$individualForm->getData();
                    $id = $companyService->create([
                        'name' => $session->name,
                        'ssn' => $data['person-ssn'],
                        'address' => $data['person-address'],
                        'zip' => $data['person-zip'],
                        'website' => null,
                        'number_of_employees' => Values::COMPANY_SIZE_PERSON,
                        'business_type' => Values::COMPANY_TYPE_PERSON,
                    ]);

                    $session->company = $id;
                    $session->company_key = 1;

                    return $this->redirect()->toRoute('access/login');
                }
                else {
                    return new ViewModel(
                        [
                        'companyForm' => $companyForm,
                        'companySelectForm' => $companySelectForm,
                        'individualForm' => $individualForm,
                        'universitySelectForm' => $universitySelectForm,
                        'panel' => 3,
                        ]
                    );
                }
            //SELECT UNIVERSITY
            //	user is selecting university
            }
            elseif (isset($post['submit-university-select'])) {
                $universitySelectForm->setData($this->getRequest()->getPost());
                if ($universitySelectForm->isValid()) {
                    $session->company = $universitySelectForm->get('university-select')->getValue();
                    $session->company_key = 0;

                    return $this->redirect()->toRoute('access/login');
                }
                else {
                    return new ViewModel(
                        [
                        'companyForm' => $companyForm,
                        'companySelectForm' => $companySelectForm,
                        'individualForm' => $individualForm,
                        'universitySelectForm' => $universitySelectForm,
                        'panel' => 2,
                        ]
                    );
                }
            }
            else {
                return $this->notFoundAction();
            }

        //QUERY
        //	get request
        }
        else {
            return new ViewModel(
                [
                'companyForm' => $companyForm,
                'companySelectForm' => $companySelectForm,
                'individualForm' => $individualForm,
                'universitySelectForm' => $universitySelectForm,
                'panel' => 0,
                ]
            );
        }
    }

    /**
     * Last installment of creating user in the system.
     *
     * @return ViewModel
     */
    public function createUserLoginAction()
    {
        $session = $this->createSessionContainer();

        $form = new NewUserPassword();
        $form->get('name')->setValue($session->email);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $session->password = $form->get('password')->getValue();
                return $this->redirect()->toRoute('access/confirm');
            } else {
                return new ViewModel(
                    [
                    'data' => (object)$session->getArrayCopy(),
                    'form' => $form
                    ]
                );
            }
        } else {
            return new ViewModel(
                [
                'data' => (object)$session->getArrayCopy(),
                'form' => $form
                ]
            );
        }
    }

    /**
     * Create user.
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function createUserConfirmAction()
    {
        $session = $this->createSessionContainer();

        $sm = $this->getServiceLocator();
        $userService = $sm->get(UserService::class);

        $id = $userService->create(
            [
            'name' => $session->name,
            'passwd' => isset($session->password)
                ? $session->password
                : $this->createPassword(10),
            'email' => $session->email,
            'title' => $session->title,
            'company_id' => $session->company,
            'key_user' => $session->company_key
            ]
        );

        $this->getEventManager()->trigger('notify', $this, [
            'action' => 'Stjornvisi\Notify\Welcome',
            'data' => (object)[
                'user_id' => $id,
                'created_company_id' => $session->company_key
                    ? $session->company
                    : null,
            ],
        ]);

        if (isset($session->password)) {
            $sm = $this->getServiceLocator();
            $auth = $sm->get(AuthenticationService::class);
            $authAdapter =  $sm->get(AuthAdapter::class);
            $authAdapter->setCredentials($session->email, $session->password);
            $result = $auth->authenticate($authAdapter);
            if ($result->isValid()) {
                $this->setRememberMeTime();
                $session->getManager()->getStorage()->clear('create_user');
                return $this->redirect()->toRoute('home');
            } else {
                throw new \Exception(implode(',', $result->getMessages()), 500);
            }
        } else {
            //GET SERVER
            //	 this check has to be done for instances where this
            //	is not run as an web-application
            $server = Module::getServerUrl();

            $user = $userService->get($id);

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
            $session->getManager()->getStorage()->clear('create_user');
            $facebooklogin = $helper->getLoginUrl();

            return $this->redirect()->toUrl($facebooklogin);
        }

    }

    /**
     * Login user.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function loginAction()
    {
        $sm = $this->getServiceLocator();
        $auth = $sm->get(AuthenticationService::class);

        //IS LOGGED IN
        //  user is logged in
        if ($auth->hasIdentity()) {
            return $this->redirect()->toRoute('notandi/index', ['id'=> $auth->getIdentity()->id]);
            //NOT LOGGED IN
            //  user is not logged in
        } else {
            $lostForm = new LostPasswordForm();
            $lostForm->setAttribute('action', $this->url()->fromRoute('access/lost-password'));

            //POST
            //  http post request, trying to log in
            if ($this->request->isPost()) {
                $form = new Login();
                $form->setData($this->getRequest()->getPost());
                //VALID
                //  valid login form
                if ($form->isValid()) {
                    //AUTH
                    //  get auth adapter, sen it the credentials,
                    //  authenticate, through the adapter and
                    //  take appropriate steps.
                    $data = $form->getData();
                    $authAdapter =  $sm->get(AuthAdapter::class);
                    $authAdapter->setCredentials($data['email'], $data['passwd']);
                    $result = $auth->authenticate($authAdapter);
                    if ($result->isValid()) {
                        $cookieValue = $sm->get(UserService::class)
                            ->createHash($auth->getIdentity()->id);
                        $cookieTTL = time() + 365 * 60 * 60 * 24;
                        $this->getResponse()->getHeaders()->addHeader($this->getSetCookie($cookieValue, $cookieTTL));
                        return $this->redirect()->toRoute('home');
                    } else {
                        $form->get('email')->setMessages(["Rangt lykilorð"]);

                        return new ViewModel(['form' => $form, 'lost' => $lostForm]);
                    }
                    //INVALID
                    //  invalid login form
                } else {
                    return new ViewModel(
                        [
                        'form' => $form,
                        'lost' => $lostForm,
                        ]
                    );
                }
                //lost-password

                //QUERY
                //  http get request, user gets login form
            } else {
                return new ViewModel(['form' => new Login(), 'lost' => $lostForm,]);
            }
        }
    }

    private function getSetCookie($value, $ttl)
    {
        $cookie = new SetCookie(self::AUTH_COOKIE, $value, $ttl, '/');
        $cookie->setSecure(isset($_SERVER['HTTPS'])) // Use secure cookie if possible
            ->setHttponly(true); // Always http only
        return $cookie;
    }
    /**
     * Logout and destroy session.
     *
     * @return \Zend\Http\Response
     */
    public function logoutAction()
    {
        $auth = $this->getServiceLocator()->get(AuthenticationService::class);
        $this->getResponse()
            ->getHeaders()
            ->addHeader($this->getSetCookie('', strtotime('-1 Year', time())));
        $auth->clearIdentity();

        return $this->redirect()->toRoute('home');
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
     * @return \Zend\Http\Response|ViewModel
     * @throws \Exception
     */
    public function callbackLoginFacebookAction()
    {
        //GET SERVER
        //	 this check has to be done for instances where this
        //	is not run as an web-application
        $server = Module::getServerUrl();

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
        if ($error == 'access_denied') {
            return new ViewModel(['error' => 'access_denied']);
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

            if (!$session) {
                throw new \Exception(
                    "Facebook session was NULL, key[{$key}], url[{$helper->getReRequestUrl()}]"
                );
            }

            $me = (new FacebookRequest(
                $session,
                'GET',
                '/me'
            ))->execute()->getGraphObject(GraphUser::className())->asArray();


            //CONNECT OLD ACCOUNT CUT-IN
            //	if $key is set, then the user is trying to connect old account to his
            //	Facebook. What we do here is to find the user based on the hash that we got
            //	back from facebook, then we inject the Facebook Auth-ID into his table just
            //	in time so that '$auth = new AuthenticationService();' line of code will pick
            //	it up and authenticate the user. This is just a little detour to quickly connect
            //	the user to a facebook account just before we authenticate him.
            if ($key) {
                $sm = $this->getServiceLocator();
                $userService = $sm->get(UserService::class);
                if (($user = $userService->getByHash($key)) != null) {
                    $userService->setOauth($user->id, $me['id'], 'facebook', $me['gender']);
                    //USER NOT FOUND
                    //	can't find the user based on hash
                } else {
                    return new ViewModel(['error' => 'user_undefined']);
                }
            }

            //AUTHENTICATE
            //	try to authenticate user against user database
            $sm = $this->getServiceLocator();
            $auth = $sm->get(AuthenticationService::class);
            $authAdapter =  $sm->get(AuthFacebook::class);
            $authAdapter->setKey($me['id']);
            $result = $auth->authenticate($authAdapter);

            //VALID
            //	user has logged in before via Facebook
            if ($result->isValid()) {
                $this->setRememberMeTime();
                return $this->redirect()->toRoute('home');
                //INVALID
                //	user hasn't logged in with facebook before. We have
                //	to initialize the connection process.
            } else {
                return new ViewModel(['error' => 'user_disconnected']);
            }

        //CAN'T LOGIN USER
        //	Facebook login library issues exception.
        //	Facebook returns an error
        } catch (FacebookRequestException $ex) {
            // When Facebook returns an error
            return new ViewModel(['error' => $ex->getMessage()]);
        //ERROR
        //	There was a more generic error
        //	When validation fails or other local issues
        } /*catch(\Exception $ex) {
        return new ViewModel(array(
        'error' => $ex->getMessage()
        ));
        }*/

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
     * @return ViewModel|array
     */
    public function requestConnectionFacebookAction()
    {
        //GET SERVER
        //	 this check has to be done for instances where this
        //	is not run as an web-application
        $server = Module::getServerUrl();

        if ($this->request->isPost()) {
            $post = $this->request->getPost()->getArrayCopy();
            $sm = $this->getServiceLocator();
            $userService = $sm->get(UserService::class);

            $user = $userService->get($post['email']);
            if ($user) {
                //FACEBOOK CONFIG
                //	get config and use it to configure facebook session
                //	and login functionality
                $config = $sm->get('Config');
                FacebookSession::setDefaultApplication(
                    $config['facebook']['appId'],
                    $config['facebook']['secret']
                );//TODO should this be in a global space
                $helper = new FacebookRedirectLoginHelper(
                    $server. AuthController::LOGIN_CALLBACK_FACEBOOK . '?key='.$user->hash
                );

                $facebooklogin = $helper->getLoginUrl();

                //NOTIFY
                //	notify user
                $this->getEventManager()->trigger(
                    'notify',
                    $this,
                    [
                        'action' => 'Stjornvisi\Notify\UserValidate',
                        'data' => (object)[
                            'user_id' => $user->id,
                            'url' => $server,
                            'facebook' => $facebooklogin
                        ],
                    ]
                );

                return new ViewModel(
                    [
                    'user' => $user,
                    'url' => $server,
                    'facebook' => $facebooklogin
                    ]
                );


                //USER NOT FOUND
            } else {
                return new ViewModel(['user' => null]);
            }
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * Request new password.
     *
     * @return ViewModel
     */
    public function lostPasswordAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get(UserService::class);
        $form = new LostPasswordForm();
        $form->setAttribute('action', $this->url()->fromRoute('access/lost-password'));
        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());
            if ($form->isValid()) {
                $user = $userService->get($form->get('email')->getValue());
                if ($user) {
                    $password = $this->createPassword(20);
                    $userService->setPassword($user->id, $password);
                    $this->getEventManager()->trigger(
                        'notify',
                        $this,
                        [
                            'action' => PasswordNotify::class,
                            'data' => (object)[
                            'recipients' => $user,
                            'password' => $password,
                            ],
                        ]
                    );
                    return new ViewModel(['message' => 'Nýtt lykilorð hefur verið sent', 'form' => $form]);
                } else {
                    $form->get('email')->setMessages(array('Notandi fannst ekki'));
                    return new ViewModel(['form' => $form, 'message' => null]);
                }
            } else {
                return new ViewModel(['form' => $form, 'message' => null]);
            }
        } else {
            return new ViewModel(['form' => $form, 'message' => null]);
        }
    }

    /**
     * Create a random password.
     *
     * @param  int $length
     * @return string
     */
    private function createPassword($length)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*()_-=+;:?";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

    /**
     * Switch user.
     *
     * If the system is running in $_ENV = development,
     * this method can switch the logged in user into what
     * ever ID passed in.
     *
     * For debugging.
     *
     * @return array|\Zend\Http\Response
     */
    public function switchUserAction()
    {
        if (Module::getApplicationEnv() != Module::ENV_DEVELOPMENT) {
            return $this->notFoundAction();
        }

        $sm = $this->getServiceLocator();
        $auth = $sm->get(AuthenticationService::class);
        /** @var  $authAdapter AuthAdapter */
        $authAdapter =  $sm->get(AuthAdapter::class);
        $authAdapter->setIdentifier($this->params('id', 0));
        $result = $auth->authenticate($authAdapter);
        if ($result->isValid()) {
            return $this->redirect()->toRoute('home');
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * @return object
     */
    private function createSessionContainer()
    {
        /** @var Container|object $session */
        $session = new Container('create_user');
        return $session;
    }

    private function setRememberMeTime()
    {
        $sessionManager = new SessionManager();
        $sessionManager->rememberMe(21600000); //250 days
    }
}
