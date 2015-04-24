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
use Psr\Log\LoggerInterface;
use Zend\EventManager\EventInterface;

class ErrorEventListener extends AbstractListenerAggregate
{
	/** @var \Psr\Log\LoggerInterface;  */
	private $logger;

	/**
	 * Create this Aggregate Event Listener.
	 *
	 * @param LoggerInterface $logger
	 */
	public function __construct(LoggerInterface $logger)
    {
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
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
			array('error'),
			array($this, 'log')
		);
    }

	/**
	 * Actually do the logging.
	 *
	 * @param EventInterface $event
	 */
	public function log(EventInterface $event)
    {
		$params = $event->getParams();
		$method = '?';
		$exception = isset($params['exception'])?$params['exception']:'';
		$sql = isset($params['sql'])?$params['sql']:array();
		$this->logger->critical(
			get_class($event->getTarget())."::{$method} - {$exception}",
			$sql
		);
	}
}
