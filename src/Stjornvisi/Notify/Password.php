<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\EventManager\EventManagerInterface;

use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Get new password sent to user in e-mail
 *
 * @package Stjornvisi\Notify
 */
class Password implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface, LoggerAwareInterface {

	/**
	 * @var \stdClass
	 */
	private $params;

	/**
	 * @var  \Psr\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * @var \Stjornvisi\Lib\QueueConnectionFactoryInterface
	 */
	private $queueFactory;

	/**
	 * @var array
	 */
	private $config;

	private $dataStore;

	/**
	 * @var \Zend\EventManager\EventManager
	 */
	protected $events;

	/**
	 * Set the data to send.
	 *
	 * @param $data
	 * @return $this|NotifyInterface
	 */
	public function setData( $data ){
		$this->params = $data;
		return $this;
	}

	/**
	 * Set logger object to monitor this handler.
	 *
	 * @param LoggerInterface $logger
	 * @return $this|NotifyInterface
	 */
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Send the notification
	 *
	 * @return $this|NotifyInterface
	 */
	public function send(){

		//VIEW
		//	create and configure view
		$child = new ViewModel(array(
			'user' => $this->params->data->recipients,
			'password' => $this->params->data->password,
		));
		$child->setTemplate('script');

		$layout = new ViewModel();
		$layout->setTemplate('layout');
		$layout->addChild($child, 'content');

		$phpRenderer = new \Zend\View\Renderer\PhpRenderer();
		$phpRenderer->setCanRenderTrees(true);

		$resolver = new \Zend\View\Resolver\TemplateMapResolver();
		$resolver->setMap(array(
			'layout' => __DIR__ . '/../../../view/layout/email.phtml',
			'script' => __DIR__ . '/../../../view/email/lost-password.phtml',
		));
		$phpRenderer->setResolver($resolver);
		foreach ($layout as $child) {
			if ($child->terminate()) {
				continue;
			}
			$child->setOption('has_parent', true);
			$result  = $phpRenderer->render($child);
			$child->setOption('has_parent', null);
			$capture = $child->captureTo();
			if (!empty($capture)) {
				if ($child->isAppend()) {
					$oldResult=$model->{$capture};
					$layout->setVariable($capture, $oldResult . $result);
				} else {
					$layout->setVariable($capture, $result);
				}
			}
		}


		//ATTEND / UN-ATTEND
		//	check if the user is registering
		//	or un-registering and render template to
		//	accommodate.

		$result = array(
			'recipient' => array(
				'name'=>$this->params->data->recipients->name,
				'address'=>$this->params->data->recipients->email),
			'subject' => "Nýtt lykilorð",
			'body' => $phpRenderer->render($layout),
			'user_id' => null,
			'type' => '',
			'entity_id' => null,
			'parameters' => '',
			'test' => true
		);


		//MAIL
		//	now we want to send this to the user/quest via e-mail
		//	so we try to connect to Queue and send a message
		//	to mail_queue
		try{
			$connection = $this->queueFactory->createConnection();
			$channel = $connection->channel();
			$channel->queue_declare('mail_queue', false, true, false, false);
			$msg = new AMQPMessage( json_encode($result),
				array('delivery_mode' => 2) # make message persistent
			);
			$this->logger->info( $this->params->data->recipients->name ." is requesting new password");
			$channel->basic_publish($msg, '', 'mail_queue');


		}catch (\Exception $e){
			$this->logger->critical(
				get_class($this) . ":send says: {$e->getMessage()}",
				$e->getTrace()
			);
		}finally{
			if( $channel ){
				$channel->close();
			}
			if( $connection ){
				$connection->close();
			}
		}

		return $this;

	}

	/**
	 * Set Queue factory
	 *
	 * @param QueueConnectionFactoryInterface $factory
	 * @return $this|NotifyInterface
	 */
	public function setQueueConnectionFactory( QueueConnectionFactoryInterface $factory ){
		$this->queueFactory = $factory;
		return $this;
	}


	public function setDateStore($config){
		$this->dataStore = $config;
		return $this;
	}

	/**
	 * Set EventManager
	 *
	 * @param EventManagerInterface $events
	 * @return $this|void
	 */
	public function setEventManager(EventManagerInterface $events){
		$events->setIdentifiers(array(
			__CLASS__,
			get_called_class(),
		));
		$this->events = $events;
		return $this;
	}

	/**
	 * Get event manager
	 *
	 * @return EventManagerInterface
	 */
	public function getEventManager(){
		if (null === $this->events) {
			$this->setEventManager(new EventManager());
		}
		return $this->events;
	}
} 