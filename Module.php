<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Stjornvisi;

use \PDO;
use Imagine;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\SlackHandler;
use PhpAmqpLib\Connection\AMQPConnection;
use Psr\Log\LoggerAwareInterface;


use Stjornvisi\Event\ActivityListener;
use Stjornvisi\Event\ErrorEventListener;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactory;
use Stjornvisi\Notify\DataStoreInterface;
use Stjornvisi\Notify\NotifyEventManagerAwareInterface;

use Stjornvisi\Service\Email;

use Stjornvisi\Auth\Adapter;
use Stjornvisi\Auth\Facebook as AuthFacebook;
use Stjornvisi\Service\JaMap;
use Stjornvisi\Service\ServiceEventManagerAwareInterface;
use Stjornvisi\Service\Conference;
use Stjornvisi\View\Helper\SubMenu;
use Stjornvisi\View\Helper\User as UserMenu;
use Stjornvisi\Event\ServiceEventListener;

use Stjornvisi\Form\NewUserCompanySelect;
use Stjornvisi\Form\NewUserCompany;
use Stjornvisi\Form\NewUserUniversitySelect;
use Stjornvisi\Form\NewUserIndividual;
use Stjornvisi\Form\NewUserCredentials;
use Stjornvisi\Form\Company as CompanyForm;

use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Application;
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


use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

use Zend\Mail\Transport\File as FileTransport;
use Zend\Mail\Transport\FileOptions;

use Zend\Http\Response as HttpResponse;

class Module
{
	/**
	 * Run for every request to the system.
	 *
	 * This function does a lot. It register all kinds of event.
	 * Logs critical error. Select correct layouts, just to
	 * name a few points....
	 *
	 * @param MvcEvent $e
	 */
	public function onBootstrap(MvcEvent $e)
	{
		$logger = $e->getApplication()->getServiceManager()->get('Logger');

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

		//SHUT DOWN
		//	register shutdown function that will log a critical message
		//
		register_shutdown_function(function () use ($logger) {
			if ($e = error_get_last()) {
				$logger->critical(
					"register_shutdown_function: ".
					$e['message'] . " in " . $e['file'] . ' line ' . $e['line']
				);
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
		/** @var  $eventManager \Zend\EventManager\Event */

		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);

		$eventManager->attach($e->getApplication()->getServiceManager()->get('Stjornvisi\Event\SystemExceptionListener'));
		$eventManager->attach($e->getApplication()->getServiceManager()->get('Stjornvisi\Event\PersistenceLoginListener'));
		$eventManager->attach($e->getApplication()->getServiceManager()->get('Stjornvisi\Event\LayoutSelectListener'));

		$eventManager->getSharedManager()->attach(
			__NAMESPACE__,
			'notify',
			$e->getApplication()->getServiceManager()->get('Stjornvisi\Event\NotifyListener')
		);
    }

	/**
	 * Load the application config.
	 *
	 * @return mixed
	 */
	public function getConfig()
	{
        return include __DIR__ . '/config/module.config.php';
    }

	/**
	 * Get how to autoload the application.
	 *
	 * @return array
	 */
	public function getAutoloaderConfig()
	{
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
	public function getServiceConfig()
	{
		return array(
			'initializers' => array(
				'DataSourceAwareInterface' => function ($instance, $sm) {
					if ($instance instanceof Lib\DataSourceAwareInterface) {
						$instance->setDataSource($sm->get('PDO'));
					}
				},
				'QueueConnectionAwareInterface' => function ($instance, $sm) {
					if ($instance instanceof QueueConnectionAwareInterface) {
						$instance->setQueueConnectionFactory($sm->get('Stjornvisi\Lib\QueueConnectionFactory'));
					}
				},
				'LoggerAwareInterface' => function ($instance, $sm) {
					if ($instance instanceof LoggerAwareInterface) {
						$instance->setLogger($sm->get('Logger'));
					}
				},
				'DataStoreInterface' => function ($instance, $sm) {
					if ($instance instanceof DataStoreInterface) {
						$instance->setDateStore($sm->get('PDO\Config'));
					}
				},
				'NotifyEventManagerAwareInterface' => function ($instance, $sm) {
					if ($instance instanceof NotifyEventManagerAwareInterface) {
						$instance->setEventManager($sm->get('ServiceEventManager'));
					}
				},
				'ServiceEventManagerAwareInterface' => function ($instance, $sm) {
					if ($instance instanceof ServiceEventManagerAwareInterface) {
						$instance->setEventManager($sm->get('ServiceEventManager'));
					}
					/**
					if ($instance instanceof EventManagerAwareInterface) {
						$eventManager = $instance->getEventManager();

						if ($eventManager instanceof EventManagerInterface) {
							$eventManager->setSharedManager($serviceLocator->get('SharedEventManager'));
						} else {
							$instance->setEventManager($serviceLocator->get('EventManager'));
						}
					}
					 */
				},
			),
			'invokables' => [
				'Stjornvisi\Service\User' 		=> 'Stjornvisi\Service\User',
				'Stjornvisi\Service\Company' 	=> 'Stjornvisi\Service\Company',
				'Stjornvisi\Service\Event' 		=> 'Stjornvisi\Service\Event',
				'Stjornvisi\Service\Group' 		=> 'Stjornvisi\Service\Group',
				'Stjornvisi\Service\News' 		=> 'Stjornvisi\Service\News',
				'Stjornvisi\Service\Board' 		=> 'Stjornvisi\Service\Board',
				'Stjornvisi\Service\Article' 	=> 'Stjornvisi\Service\Article',
				'Stjornvisi\Service\Page' 		=> 'Stjornvisi\Service\Page',
				'Stjornvisi\Service\Values' 	=> 'Stjornvisi\Service\Values',
				'Stjornvisi\Service\Conference' => 'Stjornvisi\Service\Conference',
				'Stjornvisi\Service\Skeleton' 	=> 'Stjornvisi\Service\Skeleton',
				'Stjornvisi\Service\Anaegjuvogin' 	=> 'Stjornvisi\Service\Anaegjuvogin',

				'Stjornvisi\Notify\Submission' 	=> 'Stjornvisi\Notify\Submission',
				'Stjornvisi\Notify\Event'		=> 'Stjornvisi\Notify\Event',
				'Stjornvisi\Notify\Password' 	=> 'Stjornvisi\Notify\Password',
				'Stjornvisi\Notify\Group'		=> 'Stjornvisi\Notify\Group',
				'Stjornvisi\Notify\All'			=> 'Stjornvisi\Notify\All',
				'Stjornvisi\Notify\Attend' 		=> 'Stjornvisi\Notify\Attend',
				'Stjornvisi\Notify\UserValidate' => 'Stjornvisi\Notify\UserValidate',
                'Stjornvisi\Notify\Digest'      => 'Stjornvisi\Notify\Digest',

				'Stjornvisi\Event\SystemExceptionListener' => 'Stjornvisi\Event\SystemExceptionListener',
				'Stjornvisi\Event\PersistenceLoginListener' => 'Stjornvisi\Event\PersistenceLoginListener',
				'Stjornvisi\Event\LayoutSelectListener' => 'Stjornvisi\Event\LayoutSelectListener',
				'Stjornvisi\Event\NotifyListener' => 'Stjornvisi\Event\NotifyListener',


				'Imagine\Image\Imagine'			=> 'Imagine\Gd\Imagine',

				'Stjornvisi\Auth\Adapter'		=> 'Stjornvisi\Auth\Adapter',
				'Stjornvisi\Auth\Facebook'		=> 'Stjornvisi\Auth\Facebook',
			],
			'aliases' => array(
				'UserService' => 'Stjornvisi\Service\User',
				'GroupService' => 'Stjornvisi\Service\Group'
			),
			'factories' => array(
				'Logger' => function ($sm) {
					$log = new Logger('stjornvisi');
					$log->pushHandler(new StreamHandler('php://stdout'));

					$evn = getenv('APPLICATION_ENV') ?: 'production';
					if ($evn == 'development') {
						//...
					} else {
						$handler = new StreamHandler('./data/log/error.json', Logger::ERROR);
						$handler->setFormatter(new \Stjornvisi\Lib\JsonFormatter());
						$log->pushHandler($handler);

						$handler = new StreamHandler('./data/log/system.log');
						$handler->setFormatter(new JsonFormatter());
						$log->pushHandler($handler);

						$log->pushHandler(new SlackHandler(
							"xoxp-3745519896-3745519908-3921078470-26445a",
							"#stjornvisi",
							"Angry Hamster",
							true,
							null,
							Logger::CRITICAL
						));
					}
					return $log;
				},
				'ServiceEventManager' => function ($sm) {

						$logger = $sm->get('Logger');
						$manager = new EventManager();

						$manager->attach(new ErrorEventListener($logger));
						$manager->attach(new ServiceEventListener($logger));
						$activityListener = new ActivityListener($logger);
						$activityListener->setQueueConnectionFactory(
							$sm->get('Stjornvisi\Lib\QueueConnectionFactory')
						);
						$manager->attach($activityListener);

						return $manager;
				},
				'Stjornvisi\Service\Map' => function ($sm) {
						return new JaMap(new Client());
				},
				'Stjornvisi\Service\Email' => function ($sm) {
					$config = $sm->get('config');
					$obj = new Email();
					$obj->setDataSource(new PDO(
						$config['tracker']['dns'],
						$config['tracker']['user'],
						$config['tracker']['password'],
						array(
							PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
							PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
							PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
						)
					));
					return $obj;

				},
				'PDO\Config' => function ($sm) {
					$config = $sm->get('config');
					return array(
						'dns' => $config['db']['dns'],
						'user' => $config['db']['user'],
						'password' => $config['db']['password'],
						'options' => array(
							PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
							PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
							PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
						)
					);
				},
				'PDO' => function ($sm) {
					$config = $sm->get('PDO\Config');
					return new PDO(
						$config['dns'],
						$config['user'],
						$config['password'],
						$config['options']
					);
				},
				'MailTransport' => function ($sm) {
					$evn = getenv('APPLICATION_ENV') ?: 'production';

					if ($evn == 'development') {
						$transport = new FileTransport();
						$transport->setOptions(new FileOptions([
							'path'      => './data/',
							'callback'  => function (FileTransport $transport) {
									return 'Message_' . microtime(true) . '.eml';
                                    },
                                ]
                            )
                        );
						return $transport;
					} else {
						$transport = new SmtpTransport();
                        $protocol = new \Zend\Mail\Protocol\Smtp();
                        $transport->setConnection($protocol);
                        return $transport;
					}
				},
				'Stjornvisi\Lib\QueueConnectionFactory' => function ($sm) {
					$config = $sm->get('config');
					$queue = new QueueConnectionFactory();
					$queue->setConfig($config['queue']);
					return $queue;
				},
				'Stjornvisi\Form\NewUserCompanySelect' => function ($sm) {
					return new NewUserCompanySelect(
						$sm->get('Stjornvisi\Service\Company')
					);
				},
				'Stjornvisi\Form\NewUserCompany' => function ($sm) {
					return new NewUserCompany(
						$sm->get('Stjornvisi\Service\Values'),
						$sm->get('Stjornvisi\Service\Company')
					);
				},
				'Stjornvisi\Form\NewUserUniversitySelect' => function ($sm) {
					return new NewUserUniversitySelect(
						$sm->get('Stjornvisi\Service\Company')
					);
				},
				'Stjornvisi\Form\NewUserIndividual' => function ($sm) {
					return new NewUserIndividual(
						$sm->get('Stjornvisi\Service\Values'),
						$sm->get('Stjornvisi\Service\Company')
					);
				},
				'Stjornvisi\Form\NewUserCredentials' => function ($sm) {
					return new NewUserCredentials(
						$sm->get('Stjornvisi\Service\Values'),
						$sm->get('Stjornvisi\Service\User')
					);
				},
				'Stjornvisi\Form\Company' => function ($sm) {
					return new CompanyForm(
						$sm->get('Stjornvisi\Service\Values'),
						$sm->get('Stjornvisi\Service\Company')
					);
				},
                'Stjornvisi\Queue' => function () {
                    $con = new AMQPConnection(
                        'localhost',
                        5672,
                        'guest',
                        'guest'
                    );
                    return $con;
                }
			),
			'shared' => array(
				'Stjornvisi\Service\Email' => false,
                'Stjornvisi\Queue' => false,
			),
		);
    }

	/**
	 * Load view helpers
	 *
	 * @return array
	 */
	public function getViewHelperConfig()
	{
		return [
			'invokables' => [
				'formelement' => 'Stjornvisi\Form\View\Helper\FormElement',
				'richelement'     => 'Stjornvisi\Form\View\Helper\RichElement',
				'imgelement'     => 'Stjornvisi\Form\View\Helper\ImgElement',
				'fileelement'     => 'Stjornvisi\Form\View\Helper\FileElement',
			],
			'factories' => [
				'subMenu' => function ($sm) {
					/** @var $sm \Zend\View\HelperPluginManager */
					return new SubMenu(
						$sm->getServiceLocator()->get('GroupService'),
						$sm->getServiceLocator()->get('UserService'),
						new AuthenticationService()
					);
				}
			],
		];
	}
}
