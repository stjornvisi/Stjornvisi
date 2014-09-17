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

use Stjornvisi\Event\SearchListenerAggregate;
use \Zend_Queue;
use \Zend_Db_Select;

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
use Stjornvisi\Event\ServiceIndexListener;
use Stjornvisi\Event\ServiceEventListener;

use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManager;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Log\Logger;
use Zend\Http\Client;
use Stjornvisi\Lib\Facebook;

use Stjornvisi\Service\User;

class Module{

    public function onBootstrap(MvcEvent $e){
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

		$logger = $e->getApplication()->getServiceManager()->get('Logger');
		$sem = $eventManager->getSharedManager();
		$sem->attach(__NAMESPACE__,'notify',function($event) use ($logger){
			$logger->info(
				"\033[1;33m".get_class($event->getTarget()).":: ".print_r($event->getParams() ,true)." - {$event->getName()}"."\033[0m"
			);
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
					$index = $sm->get('Search\Index\Search');
                    $manager = new EventManager();
					$manager->attach( new ServiceEventListener($logger) );
					$manager->attach( new ServiceIndexListener($index) );
                    return $manager;
                },
				/*'Search\Index\Search' => function(){
					$index = null;
					try{
						$index = \ZendSearch\Lucene\Lucene::open('./data/search/');
					}catch (\ZendSearch\Lucene\Exception\RuntimeException $e){
						$index = \ZendSearch\Lucene\Lucene::create('./data/search/');
					}
					return $index;
				},*/
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
				'Facebook' => function($sm){
					$config = $sm->get('config');
					return new Facebook($config['facebook']);
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
				'Stjornvisi\Queue\Mail' => function(){
					return new Zend_Queue('Db', array(
						'name' => 'mail-queue',
						'driverOptions' => array(
							'host'      => '127.0.0.1',
							'username'  => 'root',
							'password'  => '',
							'dbname'    => 'stjornvisi_production',
							'type'      => 'pdo_mysql',
							'port'      => 3306, // optional parameter.
						),
						'options' => array(
							// use Zend_Db_Select for update, not all databases can support this
							// feature.
							Zend_Db_Select::FOR_UPDATE => true
						)
					));
				},
				'Stjornvisi\Queue\Facebook\Album' => function(){
						return new Zend_Queue('Db', array(
							'name' => 'facebook-album-queue',
							'driverOptions' => array(
								'host'      => '127.0.0.1',
								'username'  => 'root',
								'password'  => '',
								'dbname'    => 'stjornvisi_production',
								'type'      => 'pdo_mysql',
								'port'      => 3306, // optional parameter.
							),
							'options' => array(
								// use Zend_Db_Select for update, not all databases can support this
								// feature.
								Zend_Db_Select::FOR_UPDATE => true
							)
						));
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
				},
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
