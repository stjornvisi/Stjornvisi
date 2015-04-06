<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/31/14
 * Time: 9:35 PM
 */

namespace Stjornvisi\Event;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\Event;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * This listener is listening for Controllers to issue
 * the 'notify' event. When they do, this listener will
 * delegate that message to queue.
 *
 * Class ActivityListener
 * @package Stjornvisi\Event
 */
class NotifyListener implements QueueConnectionAwareInterface, LoggerAwareInterface
{
	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	protected $factory;

	/**
	 * @var \Zend\Stdlib\CallbackHandler[]
	 */
	protected $listeners = array();

	/**
	 * Allow calls as this would be a function.
	 *
	 * @param Event $event
	 */
	public function __invoke(Event $event)
	{
		try {
			$connection = $this->factory->createConnection();
			$channel = $connection->channel();

			$channel->queue_declare('notify_queue', false, true, false, false);
			$msg = new AMQPMessage(json_encode($event->getParams()), ['delivery_mode' => 2]);

			$channel->basic_publish($msg, '', 'notify_queue');

			$channel->close();
			$connection->close();
		} catch (\Exception $e) {
			$this->logger->critical("Notify Service Event says: {$e->getMessage()}", $e->getTrace());
		}
	}

	/**
	 * Sets a logger instance on the object
	 *
	 * @param LoggerInterface $logger
	 * @return null
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Set Queue factory
	 * @param QueueConnectionFactoryInterface $factory
	 * @return mixed
	 */
	public function setQueueConnectionFactory(QueueConnectionFactoryInterface $factory)
	{
		$this->factory = $factory;
	}
}
