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

	/** @var \Zend\Log\LoggerInterface  */
	private $logger;

	/**
	 * Create this Aggregate Event Listener.
	 *
	 * @param LoggerInterface $logger
	 */
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
        $this->listeners[] = $events->attach(
			array('create','read','update','delete','index'),
			array($this, 'log')
		);
    }

	/**
	 * Actually do the logging.
	 *
	 * @param EventInterface $event
	 */
	public function log(EventInterface $event){
		$params = $event->getParams();
		$method = isset($params[0])?$params[0]:'';
		$this->logger->info(
			get_class($event->getTarget())."::{$method} - {$event->getName()}"
		);

	}
}