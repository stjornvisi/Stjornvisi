<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/31/14
 * Time: 9:48 PM
 */

namespace Stjornvisi\Event;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;

use Psr\Log\LoggerInterface;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ServiceIndexListener extends AbstractListenerAggregate {

	/** @var \Psr\Log\LoggerInterface;  */
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
		$this->listeners[] = $events->attach('index', array($this, 'index'));
	}

	/**
	 * Send a message to Queue about indexing entry.
	 *
	 * @param EventInterface $event
	 */
	public function index(EventInterface $event){

		try{
			$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('search-index', false, false, false, false);

			$params = $event->getParams();

			$msg = new AMQPMessage( json_encode($params) );
			$channel->basic_publish($msg, '', 'search-index');
			$channel->close();
			$connection->close();

		}catch (\Exception $e){
			$this->logger->warn( "Queue Service when indexing entries: ".$e->getMessage() );
		}
	}
} 