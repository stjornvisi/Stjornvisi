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

use Stjornvisi\Service\Company;
use Stjornvisi\Service\Event;
use Stjornvisi\Service\Group;
use Stjornvisi\Service\News;
use Stjornvisi\Service\Board;
use Stjornvisi\Service\Article;
use Stjornvisi\Service\Page;
use Stjornvisi\Auth\Adapter;
use Stjornvisi\Auth\Facebook;
use Stjornvisi\Service\JaMap;
use Stjornvisi\Service\Values;
use Stjornvisi\Mail\Service\File;
use Zend\EventManager\EventManager;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Log\Logger;
use Zend\Http\Client;

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

    public function getConfig()
    {
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
                    $manager->attach(array('create','read','update','delete'),function($event) use($logger){
						$eventType = '';
						switch( $event->getName() ){
							case 'create':
								$eventType = "\033[0m\033[0;30\033[42m create \033[0m";
								break;
							case 'read':
								$eventType = "\033[0m\033[0;30\033[43m read \033[0m";
								break;
							case 'update':
								$eventType = "\033[0m\033[1;37\033[44m update \033[0m";
								break;
							case 'delete':
								$eventType = "\033[0m\033[1;37\033[46m delete \033[0m";
								break;
						}
                        $logger->info(
                            "\033[1;36m".get_class($event->getTarget())."::{$event->getParams()[0]} - {$eventType}"
                        );
                    });

                    return $manager;
                },
				'Search\Index\Search' => function(){
					$index = null;
					try{
						$index = \ZendSearch\Lucene\Lucene::open('./data/search/');
					}catch (\ZendSearch\Lucene\Exception\RuntimeException $e){
						$index = \ZendSearch\Lucene\Lucene::create('./data/search/');
					}
					return $index;
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
                        return new Facebook($sm->get('PDO'));
                    },
                'PDO' => function(){
                    return new PDO(
                        'mysql:dbname=stjornvisi_production;host=127.0.0.1',
                        'root',
                        '',
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
            )
        );
    }
}
