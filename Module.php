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
use \PDO;

//use Stjornvisi\Event\SearchListenerAggregate;
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
use Stjornvisi\Service\Values;
use Stjornvisi\Mail\Service\File;
use Stjornvisi\View\Helper\SubMenu;
use Stjornvisi\View\Helper\User as UserMenu;
use Stjornvisi\Event\ServiceIndexListener;
use Stjornvisi\Event\ServiceEventListener;

use Stjornvisi\Form\NewUserCompanySelect;
use Stjornvisi\Form\NewUserCompany;
use Stjornvisi\Form\NewUserUniversitySelect;
use Stjornvisi\Form\NewUserIndividual;
use Stjornvisi\Form\NewUserCredentials;

use Stjornvisi\Notify\Submission as SubmissionNotify;
use Stjornvisi\Notify\Event as EventNotify;
use Stjornvisi\Notify\Password as PasswordNotify;
use Stjornvisi\Notify\Group as GroupNotify;
use Stjornvisi\Notify\Attend as AttendNotify;

use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManager;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Log\Logger;
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

class Module{

    public function onBootstrap(MvcEvent $e){


		$config = $e->getApplication()
			->getServiceManager()
			->get('Configuration');

		$sessionConfig = new SessionConfig();
		$sessionConfig->setOptions($config['session']);
		$sessionManager = new SessionManager($sessionConfig);
		$sessionManager->start();


        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

		$logger = $e->getApplication()->getServiceManager()->get('Logger');
		$connectionFactory = $e->getApplication()->getServiceManager()->get('Stjornvisi\Lib\QueueConnectionFactory');
		$sem = $eventManager->getSharedManager();

		//NOTIFY
		//	listen for the 'notify' event, usually coming from the
		//	controllers. This indicates that the application want to
		//	notify a user using external service like e-mail or maybe facebook.
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
				$logger->warn("Queue Service says: {$e->getMessage()}");
			}
		});


    }

    public function getConfig(){
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig(){
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

	public function getServiceConfig(){
        return array(
            'factories' => array(
                'Logger' => function($sm){
                        $logger = new Logger;
                        $logger->addWriter('stream', null, array('stream' => 'php://stdout'));
                        return $logger;
                },
                'ServiceEventManager' => function($sm){
                    $logger = $sm->get('Logger');
                    $manager = new EventManager();
					$manager->attach( new ServiceEventListener($logger) );
					$manager->attach( new ServiceIndexListener($logger) );
                    return $manager;
                },
                'CsvStrategy' => 'Stjornvisi\View\Strategy\CsvFactory',
                'Stjornvisi\Service\Values' => function($sm){
                    return new Values();
                },
                'Stjornvisi\Service\Map' => function($sm){
                    return new JaMap( new Client() );
                },
                'Stjornvisi\Mail\Service' => function($sm){
                    $logger = new Logger;
                    $logger->addWriter('stream', null, array('stream' => 'php://stdout'));
                    return new File( $logger );
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
				/*
				'Facebook' => function($sm){
					$config = $sm->get('config');
					return new Facebook($config['facebook']);
				},*/
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
						$obj->setLogger( $sm->get('Logger') );
						return $obj;
				},
				'Stjornvisi\Notify\Event' => function($sm){
						$obj = new EventNotify(
							$sm->get('Stjornvisi\Service\User'),
							$sm->get('Stjornvisi\Service\Event')
						);
						$obj->setLogger( $sm->get('Logger') );
						return $obj;
					},
				'Stjornvisi\Notify\Password' => function($sm){
						$obj = new PasswordNotify();
						$obj->setLogger( $sm->get('Logger') );
						return $obj;
					},
				'Stjornvisi\Notify\Group' => function($sm){
						$obj = new GroupNotify();
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
				'MailOptions' => function($sm){
					/*
					return  new SmtpOptions(array(
						'name'              => 'localhost.localdomain',
						'host'              => '127.0.0.1',
						'connection_class'  => 'login',
						'connection_config' => array(
							'username' => 'user',
							'password' => 'pass',
						))
					);
					*/
					return new FileOptions(array(
							'path'              => './data/mail/',
							'callback'  => function (FileTransport $transport) {
									return 'Message_' . microtime(true) . '_' . mt_rand() . '.txt';
								},
						));
				},
				'MailTransport' => function($sm){
					//$transport = new SmtpTransport();
					//$transport->setOptions($sm->get('MailOptions'));
					//return $transport;
					$transport = new FileTransport();
					$transport->setOptions($sm->get('MailOptions'));
					return $transport;
				},
				'Stjornvisi\Lib\QueueConnectionFactory' => function($sm){
					$config = $sm->get('config');
					$queue = new QueueConnectionFactory();
					$queue->setConfig($config['queue']);
					return $queue;
				},



				'Stjornvisi\Form\NewUserCompanySelect' => function($sm){
					return new NewUserCompanySelect( $sm->get('Stjornvisi\Service\Company') );
				},
				'Stjornvisi\Form\NewUserCompany' => function($sm){
					return new NewUserCompany( $sm->get('Stjornvisi\Service\Values') );
				},
				'Stjornvisi\Form\NewUserUniversitySelect' => function($sm){
					return new NewUserUniversitySelect( $sm->get('Stjornvisi\Service\Company') );
				},
				'Stjornvisi\Form\NewUserIndividual' => function($sm){
					return new NewUserIndividual( $sm->get('Stjornvisi\Service\Values') );
				},
				'Stjornvisi\Form\NewUserCredentials' => function($sm){
					return new NewUserCredentials(
						$sm->get('Stjornvisi\Service\Values'),
						$sm->get('Stjornvisi\Service\User')
					);
				},

            )
        );
    }

	public function getViewHelperConfig(){
		return array(
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

	public function init(ModuleManager $mm){
		$auth = new AuthenticationService();
		$mm->getEventManager()->getSharedManager()->attach(__NAMESPACE__, 'dispatch', function($e) use ($auth) {
			if( !$auth->hasIdentity() ){
				$e->getTarget()->layout('layout/anonymous');
			}
		});
	}
}
