<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/31/14
 * Time: 9:35 PM
 */

namespace Stjornvisi\Event;


use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\LoggerInterface;
use Zend\EventManager\EventInterface;

class ServiceEventListener extends AbstractListenerAggregate {

	private $logger;
	public function __construct( LoggerInterface $logger ){
		$this->logger = $logger;
	}

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events){
        $this->listeners[] = $events->attach(array('create','read','update','delete','index'), array($this, 'log'));
    }

	public function log(EventInterface $event){
		$params = $event->getParams();
		$method = isset($params[0])?$params[0]:'';
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
			case 'index':
				$eventType = "\033[0m\033[1;31\033[40m index \033[0m";
				break;
		}
		$this->logger->info(
			"\033[1;36m".get_class($event->getTarget())."::{$method} - {$eventType}"
		);
	}
}