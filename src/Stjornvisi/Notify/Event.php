<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 28/09/14
 * Time: 22:42
 */

namespace Stjornvisi\Notify;

use Psr\Log\LoggerInterface;

use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;
use Stjornvisi\Service\User;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\EventManager\EventManagerInterface;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Emails sent from an event to all or attendees.
 *
 * @package Stjornvisi\Notify
 */
class Event implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface {

	/**
	 * @var \stdClass
	 */
	private $params;

	/**
	 * @var \Stjornvisi\Service\Event
	 */
	private $event;

	/**
	 * @var \Stjornvisi\Service\User
	 */
	private $user;

	/**
	 * @var  \Psr\Log\LoggerInterface;
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
	 * Set the data that is coming from the
	 * producer.
	 *
	 * @param $data
	 * @return $this|NotifyInterface
	 */
	public function setData( $data ){
		$this->params = $data;
		return $this;
	}

	/**
	 * Set logger instance
	 *
	 * @param LoggerInterface $logger
	 * @return $this|NotifyInterface
	 */
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Send notification to what ever media or outlet
	 * required by the implementer.
	 *
	 * @return $this|NotifyInterface
	 */
	public function send(){

		$pdo = new \PDO(
			$this->dataStore['dns'],
			$this->dataStore['user'],
			$this->dataStore['password'],
			$this->dataStore['options']
		);

		$this->user = new User();
		$this->user->setDataSource( $pdo )
			->setEventManager( $this->getEventManager() );
		$this->event = new \Stjornvisi\Service\Event();
		$this->event->setDataSource( $pdo )
			->setEventManager( $this->getEventManager() );

		$emailId = md5( time() + rand(0,1000) );

		//EVENT
		//	first of all, find the event in question
		$event = $this->event->get( $this->params->data->event_id );

		//GROUPS
		//	next we need to extract all groups associated with the event
		//	and then I need their IDs
		$groupIds = array_map(function($i){
			return $i->id;
		}, $event->groups );

		//TEST
		//	this is just a test message so we send it just to the user in question
		if( $this->params->data->test ){
			$users = array( $this->user->get( $this->params->data->user_id ));
		//REAL
		//	this is the real thing.
		}else{
			$users = ( $this->params->data->recipients == 'allir' )
				? $this->user->getUserMessageByGroup( $groupIds )
				: $this->user->getUserMessageByEvent($event->id) ;
		}

		$this->logger->info( get_class($this) . " " . (count($users)) . " user will get an email" .
			"in connection with event {$event->subject}:{$event->id}");


		$paragrapher = new \Stjornvisi\View\Helper\Paragrapher();

		//VIEW
		//	create and configure view
		$child =new ViewModel(array(
			'user' => null,
			'event' => $event,
			'body' => $paragrapher->__invoke($this->params->data->body)
		));
		$child->setTemplate('event');


		$layout = new ViewModel();
		$layout->setTemplate('layout');
		$layout->addChild($child, 'content');

		$phpRenderer = new \Zend\View\Renderer\PhpRenderer();
		$phpRenderer->setCanRenderTrees(true);

		$resolver = new \Zend\View\Resolver\TemplateMapResolver();
		$resolver->setMap(array(
			'layout' => __DIR__ . '/../../../view/layout/email.phtml',
			'event' => __DIR__ . '/../../../view/email/event.phtml',
		));
		$phpRenderer->setResolver($resolver);




		//CONNECT TO QUEUE
		//	try to connect to RabbitMQ
		try{
			$connection = $this->queueFactory->createConnection();
			$channel = $connection->channel();
			$channel->queue_declare('mail_queue', false, true, false, false);

			//FOR EVER USER
			//	for every user: render email template, create message object and
			//	send to mail-queue
			foreach( $users as $user ){

				$child->setVariable('user',$user);
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
				$result = array(
					'recipient' => array('name'=>$user->name, 'address'=>$user->email),
					'subject' => $this->params->data->subject,
					'body' => $phpRenderer->render($layout),
					'id' => $emailId,
					'user_id' => md5( (string)$emailId . $user->email  ),
					'type' => 'Event',
					'entity_id' => $event->id,
					'parameters' => $this->params->data->recipients,
					'test' => $this->params->data->test
				);
				$msg = new AMQPMessage( json_encode($result),
					array('delivery_mode' => 2) # make message persistent
				);

				$this->logger->info( get_class($this) . " user {$user->email} will get an email" .
					"in connection with event {$event->subject}:{$event->id}");

				$channel->basic_publish($msg, '', 'mail_queue');
			}

		}catch (\Exception $e){
			$this->logger->critical(
				"Mail Queue Service says: {$e->getMessage()}",
				$e->getTrace()
			);
		}finally{
			if($channel){
				$channel->close();
			}
			if($connection){
				$connection->close();
			}

			$pdo = null;

			$this->user = null;
			$this->event = null;
		}
		return $this;
	}

	/**
	 * Set Queue factory.
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