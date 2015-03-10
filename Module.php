<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Stjornvisi;

use Imagine;
use Monolog\Handler\SlackHandler;
use \PDO;


use Stjornvisi\Event\ActivityListener;
use Stjornvisi\Event\ErrorEventListener;
use Stjornvisi\Lib\QueueConnectionFactory;
use Stjornvisi\Service\Company;
use Stjornvisi\Service\Event;
use Stjornvisi\Service\Group;
use Stjornvisi\Service\News;
use Stjornvisi\Service\Board;
use Stjornvisi\Service\Article;
use Stjornvisi\Service\Page;
use Stjornvisi\Auth\Adapter;
use Stjornvisi\Auth\Facebook as AuthFacebook;
use Stjornvisi\Service\JaMap;
use Stjornvisi\Service\Skeleton;
use Stjornvisi\Service\Conference;
use Stjornvisi\Service\Values;
use Stjornvisi\View\Helper\SubMenu;
use Stjornvisi\View\Helper\User as UserMenu;
use Stjornvisi\Event\ServiceIndexListener;
use Stjornvisi\Event\ServiceEventListener;

use Stjornvisi\Form\NewUserCompanySelect;
use Stjornvisi\Form\NewUserCompany;
use Stjornvisi\Form\NewUserUniversitySelect;
use Stjornvisi\Form\NewUserIndividual;
use Stjornvisi\Form\NewUserCredentials;
use Stjornvisi\Form\Company as CompanyForm;

use Stjornvisi\Notify\Submission as SubmissionNotify;
use Stjornvisi\Notify\Event as EventNotify;
use Stjornvisi\Notify\Password as PasswordNotify;
use Stjornvisi\Notify\Group as GroupNotify;
use Stjornvisi\Notify\All as AllNotify;
use Stjornvisi\Notify\Attend as AttendNotify;

use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManager;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Zend\Http\Client;

use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\EventManager\EventInterface;

use Stjornvisi\Lib\Facebook;
use Stjornvisi\Service\User;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

use Zend\Mail\Transport\File as FileTransport;
use Zend\Mail\Transport\FileOptions;

use Zend\Http\Response as HttpResponse;

class Module{

	/**
	 * Run for every request to the system.
	 *
	 * This function does a lot. It register all kinds of event.
	 * Logs critical error. Select correct layouts, just to
	 * name a few points....
	 *
	 * @param MvcEvent $e
	 */
	public function onBootstrap(MvcEvent $e){

		$logger = $e->getApplication()->getServiceManager()->get('Logger');

		//SHUT DOWN
		//	register shutdown function that will log a critical message
		//
		register_shutdown_function(function () use ($logger){
			if ($e = error_get_last()) {
				$logger->critical("register_shutdown_function:".$e['message'] . " in " . $e['file'] . ' line ' . $e['line']);
				echo "Smá vandræði";
			}
		});

		//EVENT MANAGER
		//	get event manager and attache event handlers to it. These
		//	event are something required for the MVC to work. And
		//	error events in the MVC application; ie. if something
		//	goes wrong in Dispatch or Rendering, these events will be called,
		//	they will  log a critical message
		$eventManager        = $e->getApplication()->getEventManager();

		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);

		$eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR, function(MvcEvent $e) use ($logger) {


			if( ($exception = $e->getParam('exception')) != false ){
				$logger->critical('EVENT_DISPATCH_ERROR:'.$exception->getMessage(),$exception->getTrace());
			}

			/*
			$logger->critical( "EVENT_DISPATCH_ERROR: ".$e->getError() .implode(",",array(
				'error'      => $e->getParam('error'),
				'identity'   => $e->getParam('identity'),
				'controller' => $e->getParam('controller'),
				'action' => $e->getParam('action'),
				'route' => $e->getParam('route'),
				'excpetion' => ($e->getParam('exception'))
						? $e->getParam('exception')->getMessage()
						: ''
			) ));

			$logger->critical( "EVENT_DISPATCH_ERROR: ".$e->getError() );

			$topexception = $e->getParam('exception');
			$exception = $e->getParam('exception');
			$errorString = "EVENT_DISPATCH_ERROR:";

			while( $exception ){
				$errorString .= ($exception->getMessage() . PHP_EOL);
				$errorString .= (print_r($exception->getTraceAsString(),true) . PHP_EOL);
				$exception = $exception->getPrevious();
			}
			$logger->critical($errorString,($topexception)?$topexception->getTrace():array());
			*/
		} );
		$eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER_ERROR, function(MvcEvent $e) use ($logger) {
			$topexception = $e->getParam('exception');
			$exception = $e->getParam('exception');
			$errorString = "EVENT_RENDER_ERROR:";
			/** @var $exception \Exception */
			while( $exception ){
				$errorString .= ($exception->getMessage() . PHP_EOL);
				$errorString .= (print_r($exception->getTraceAsString(),true) . PHP_EOL);
				$exception = $exception->getPrevious();
			}
			$logger->critical($errorString,($topexception)?$topexception->getTrace():array());
		} );

		//CONFIG
		//	get config values from the application
		//	config files.
		$config = $e->getApplication()
			->getServiceManager()
			->get('Configuration');

		//SESSION
		//	config and start session
		$sessionConfig = new SessionConfig();
		$sessionConfig->setOptions($config['session']);
		$sessionManager = new SessionManager($sessionConfig);
		$sessionManager->start();

		//AUTH
		//	select correct layout based on the user
		//	and if he is on the landing page and if he
		//	is logged in
		$auth = new AuthenticationService();
		$eventManager->attach('render',function($e) use ($auth){
			/** @var $e \Zend\Mvc\MvcEvent  */
			if( !$auth->hasIdentity() ){
				$router = $e->getRouteMatch();
				if( method_exists($router,'getMatchedRouteName') && $router->getMatchedRouteName() == 'home' ){
					$e->getViewModel()->setTemplate('layout/landing');
				}else{
					$e->getViewModel()->setTemplate('layout/anonymous');
				}
			}
		});

		//QUEUE CONNECTION FACTORY
		//	we are gonna send messages to the notify-queue
		//	so we need an instance of it
		$connectionFactory = $e->getApplication()
			->getServiceManager()
			->get('Stjornvisi\Lib\QueueConnectionFactory');

		//NOTIFY VIA SHARED EVENTS
		//	listen for the 'notify' event, usually coming from the
		//	controllers. This indicates that the application want to
		//	notify a user using external service like e-mail or maybe facebook.
		$sem = $eventManager->getSharedManager();
		$sem->attach(__NAMESPACE__,'notify',function($event) use ($logger,$connectionFactory){

			try{
				$connection = $connectionFactory->createConnection();
				$channel = $connection->channel();

				$channel->queue_declare('notify_queue', false, true, false, false);
				$msg = new AMQPMessage( json_encode($event->getParams()),
					array('delivery_mode' => 2) # make message persistent
				);

				$channel->basic_publish($msg, '', 'notify_queue');

				$channel->close();
				$connection->close();
			}catch (\Exception $e){
				$logger->critical("Notify Service Event says: {$e->getMessage()}",$e->getTrace());
			}
		});

    }

	/**
	 * Load the application config.
	 *
	 * @return mixed
	 */
	public function getConfig(){
        return include __DIR__ . '/config/module.config.php';
    }

	/**
	 * Get how to autoload the application.
	 *
	 * @return array
	 */
	public function getAutoloaderConfig(){
        return array(
			'Zend\Loader\ClassMapAutoloader' => array(
				__DIR__ . '/vendor/composer/autoload_classmap.php',
			),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

	/**
	 * Load the services.
	 *
	 * @return array
	 */
	public function getServiceConfig(){
        return array(
            'factories' => array(
                'Logger' => function($sm){
					$log = new Logger('stjornvisi');
					$log->pushHandler(new StreamHandler('php://stdout'));
					$log->pushHandler(new StreamHandler('./data/log/system.log'));
					$log->pushHandler(new SlackHandler(
						"xoxp-3745519896-3745519908-3921078470-26445a",
						"#stjornvisi",
						"Angry Hamster",
						true,
						null,
						Logger::CRITICAL
					));
					return $log;
                },
                'ServiceEventManager' => function($sm){
                    $logger = $sm->get('Logger');
                    $manager = new EventManager();

					$manager->attach( new ErrorEventListener($logger) );
					$manager->attach( new ServiceEventListener($logger) );
					$activityListener = new ActivityListener($logger);
					$activityListener->setQueueConnectionFactory(
						$sm->get('Stjornvisi\Lib\QueueConnectionFactory')
					);
					$manager->attach( $activityListener );
                    return $manager;
                },
                'Stjornvisi\Service\Values' => function($sm){
                    return new Values();
                },
                'Stjornvisi\Service\Map' => function($sm){
                    return new JaMap( new Client() );
                },
                'Stjornvisi\Auth\Adapter' => function($sm){
                    return new Adapter($sm->get('PDO'));
                 },
                'Stjornvisi\Auth\Facebook' => function($sm){
                        return new AuthFacebook($sm->get('PDO'));
                },
                'PDO' => function($sm){
					$config = $sm->get('config');
                    return new PDO(
                        $config['db']['dns'],
						$config['db']['user'],
						$config['db']['password'],
                        array(
                            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
							//PDO::ATTR_EMULATE_PREPARES => false,
                        ));
                 },
				'Imagine\Image\Imagine' => function(){
						return new Imagine\Gd\Imagine();
				},
                'Stjornvisi\Service\User' => function($sm){
                        $obj = new User( $sm->get('PDO') );
                        $obj->setEventManager( $sm->get('ServiceEventManager') );
                        return $obj;
                },
                'Stjornvisi\Service\Company' => function($sm){
                        $obj = new Company( $sm->get('PDO') );
                        $obj->setEventManager( $sm->get('ServiceEventManager') );
                        return $obj;
                },
                'Stjornvisi\Service\Event' => function($sm){
                        $obj = new Event( $sm->get('PDO') );
                        $obj->setEventManager( $sm->get('ServiceEventManager') );
                        return $obj;
                },
                'Stjornvisi\Service\Group' => function($sm){
                        $obj = new Group( $sm->get('PDO') );
                        $obj->setEventManager( $sm->get('ServiceEventManager') );
                        return $obj;
                },
                'Stjornvisi\Service\News' => function($sm){
                        $obj = new News( $sm->get('PDO') );
                        $obj->setEventManager( $sm->get('ServiceEventManager') );
                        return $obj;
                },
				'Stjornvisi\Service\Board' => function($sm){
						$obj = new Board( $sm->get('PDO') );
						$obj->setEventManager( $sm->get('ServiceEventManager') );
						return $obj;
				},
                'Stjornvisi\Service\Article' => function($sm){
                        $obj = new Article( $sm->get('PDO') );
                        $obj->setEventManager( $sm->get('ServiceEventManager') );
                        return $obj;
                },
				'Stjornvisi\Service\Page' => function($sm){
						$obj = new Page( $sm->get('PDO') );
						$obj->setEventManager( $sm->get('ServiceEventManager') );
						return $obj;
				},
				'Stjornvisi\Notify\Submission' => function($sm){
						$obj = new SubmissionNotify(
							$sm->get('Stjornvisi\Service\User'),
							$sm->get('Stjornvisi\Service\Group')
						);
						$obj->setQueueConnectionFactory(
							$sm->get('Stjornvisi\Lib\QueueConnectionFactory')
						);
						$obj->setLogger( $sm->get('Logger') );
						return $obj;
				},
				'Stjornvisi\Notify\Event' => function($sm){
						$obj = new EventNotify(
							$sm->get('Stjornvisi\Service\User'),
							$sm->get('Stjornvisi\Service\Event')
						);
						$obj->setQueueConnectionFactory(
							$sm->get('Stjornvisi\Lib\QueueConnectionFactory')
						);
						$obj->setLogger( $sm->get('Logger') );
						return $obj;
				},
				'Stjornvisi\Notify\Password' => function($sm){
						$obj = new PasswordNotify();
						$obj->setLogger( $sm->get('Logger') );
						$obj->setQueueConnectionFactory(
							$sm->get('Stjornvisi\Lib\QueueConnectionFactory')
						);
						return $obj;
				},
				'Stjornvisi\Notify\Group' => function($sm){
						$obj = new GroupNotify(
							$sm->get('Stjornvisi\Service\User'),
							$sm->get('Stjornvisi\Service\Group')
						);
						$obj->setQueueConnectionFactory(
							$sm->get('Stjornvisi\Lib\QueueConnectionFactory')
						);
						$obj->setLogger( $sm->get('Logger') );
						return $obj;
				},
				'Stjornvisi\Notify\All' => function($sm){
						$obj = new AllNotify(
							$sm->get('Stjornvisi\Service\User')
						);
						$obj->setQueueConnectionFactory(
							$sm->get('Stjornvisi\Lib\QueueConnectionFactory')
						);
						$obj->setLogger( $sm->get('Logger') );
						return $obj;
				},
				'Stjornvisi\Notify\Attend' => function($sm){
						$obj = new AttendNotify(
							$sm->get('Stjornvisi\Service\User'),
							$sm->get('Stjornvisi\Service\Event')
						);
						$obj->setQueueConnectionFactory(
							$sm->get('Stjornvisi\Lib\QueueConnectionFactory')
						);
						$obj->setLogger( $sm->get('Logger') );
						return $obj;
				},
				'Stjornvisi\Notify\UserValidate' => function($sm){
						$obj = new \Stjornvisi\Notify\UserValidate(
							$sm->get('Stjornvisi\Service\User')
						);
						$obj->setQueueConnectionFactory(
							$sm->get('Stjornvisi\Lib\QueueConnectionFactory')
						);
						$obj->setLogger( $sm->get('Logger') );
						return $obj;
				},
				'MailTransport' => function($sm){

					$transport = new SmtpTransport();
					//$transport->setOptions(new SmtpOptions(array(
					//	'name'              => 'localhost.localdomain',
					//	'host'              => '127.0.0.1',
					//)));
					$protocol = new \Zend\Mail\Protocol\Smtp();
					$transport->setConnection( $protocol );
					return $transport;


					/*
					$transport = new FileTransport();
					$transport->setOptions(new FileOptions(array(
						'path'      => './data/',
						'callback'  => function (FileTransport $transport) {
								return 'Message_' . microtime(true) . '.eml';
							},
					)));
					return $transport;
					*/


				},
				'Stjornvisi\Lib\QueueConnectionFactory' => function($sm){
					$config = $sm->get('config');
					$queue = new QueueConnectionFactory();
					$queue->setConfig($config['queue']);
					return $queue;
				},

				'Stjornvisi\Form\NewUserCompanySelect' => function($sm){
					return new NewUserCompanySelect(
						$sm->get('Stjornvisi\Service\Company')
					);
				},
				'Stjornvisi\Form\NewUserCompany' => function($sm){
					return new NewUserCompany(
						$sm->get('Stjornvisi\Service\Values'),
						$sm->get('Stjornvisi\Service\Company')
					);
				},
				'Stjornvisi\Form\NewUserUniversitySelect' => function($sm){
					return new NewUserUniversitySelect(
						$sm->get('Stjornvisi\Service\Company')
					);
				},
				'Stjornvisi\Form\NewUserIndividual' => function($sm){
					return new NewUserIndividual(
						$sm->get('Stjornvisi\Service\Values'),
						$sm->get('Stjornvisi\Service\Company')
					);
				},
				'Stjornvisi\Form\NewUserCredentials' => function($sm){
					return new NewUserCredentials(
						$sm->get('Stjornvisi\Service\Values'),
						$sm->get('Stjornvisi\Service\User')
					);
				},
				'Stjornvisi\Form\Company' => function($sm){
					return new CompanyForm(
						$sm->get('Stjornvisi\Service\Values'),
						$sm->get('Stjornvisi\Service\Company')
					);
				},

				'Stjornvisi\Service\Skeleton' => function($sm){
					return new Skeleton();
				},
				'Stjornvisi\Service\Conference' => function($sm){
					$obj = new Conference( $sm->get('PDO') );
					$obj->setEventManager( $sm->get('ServiceEventManager') );
					return $obj;
				},

            )
        );
    }

	/**
	 * Load view helpers
	 *
	 * @return array
	 */
	public function getViewHelperConfig(){
		return array(
			'invokables' => array(
				'formelement' => 'Stjornvisi\Form\View\Helper\FormElement',
				'richelement'     => 'Stjornvisi\Form\View\Helper\RichElement',
				'imgelement'     => 'Stjornvisi\Form\View\Helper\ImgElement',
				'fileelement'     => 'Stjornvisi\Form\View\Helper\FileElement',
			),
			'factories' => array(
				'subMenu' => function($sm){
					return new SubMenu(
						$sm->getServiceLocator()->get('Stjornvisi\Service\Group'),
						$sm->getServiceLocator()->get('Stjornvisi\Service\User'),
						new AuthenticationService()
					);
				}
			),
		);
	}

}
