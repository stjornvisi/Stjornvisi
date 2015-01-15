<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 28/09/14
 * Time: 22:42
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Service\User as UserService;
use Stjornvisi\Service\Event as EventService;

use Zend\Log\LoggerInterface;

use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Event
 * @package Stjornvisi\Notify
 */
class Event implements NotifyInterface, QueueConnectionAwareInterface {

	/** @var \stdClass */
	private $params;

	/** @var \Stjornvisi\Service\Event */
	private $event;

	/** @var \Stjornvisi\Service\User */
	private $user;

	/** @var  \Zend\Log\LoggerInterface */
	private $logger;

	/** @var \Stjornvisi\Lib\QueueConnectionFactoryInterface  */
	private $queueFactory;

	/**
	 * Create an instance of this handler. It requires
	 * two services.
	 *
	 * @param UserService $userService
	 * @param EventService $eventService
	 */
	public function __construct( UserService $userService, EventService $eventService ){
		$this->user = $userService;
		$this->event = $eventService;
	}

	/**
	 * Set the data that is coming from the
	 * producer.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function setData( $data ){
		$this->params = $data;
	}

	/**
	 * Set logger instance
	 *
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
	}

	/**
	 * Send notification to what ever media or outlet
	 * required by the implementer.
	 *
	 * @return mixed
	 */
	public function send(){

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

		//VIEW
		//	create everything that is needed to render the
		//	HTML of the email
		$renderer = new PhpRenderer();
		$resolver = new Resolver\AggregateResolver();
		$renderer->setResolver($resolver);
		$map = new Resolver\TemplateMapResolver(array(
			'layout'      =>__DIR__ . '/../../../view/layout/email.phtml',
		));
		$stack = new Resolver\TemplatePathStack(array(
			'script_paths' => array(
				__DIR__ . '/../../../view/email/',
			)
		));
		$resolver->attach($map)->attach($stack);




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

				$model = new ViewModel(array(
					'user' => $user,
					'event' => $event,
					'body' => $this->params->data->body
				));
				$model->setTemplate('event');
				$result = array(
					'recipient' => array('name'=>$user->name, 'address'=>$user->email),
					'subject' => $this->params->data->subject,
					'body' => $renderer->render($model)
				);
				$msg = new AMQPMessage( json_encode($result),
					array('delivery_mode' => 2) # make message persistent
				);

				$this->logger->info( get_class($this) . " user {$user->email} will get an email" .
					"in connection with event {$event->subject}:{$event->id}");

				$channel->basic_publish($msg, '', 'mail_queue');
			}

		}catch (\Exception $e){
			$this->logger->warn("Mail Queue Service says: {$e->getMessage()}");
		}finally{
			if($channel){
				$channel->close();
			}
			if($connection){
				$connection->close();
			}
		}
	}

	/**
	 * Set Queue factory
	 * @param QueueConnectionFactoryInterface $factory
	 * @return mixed
	 */
	public function setQueueConnectionFactory( QueueConnectionFactoryInterface $factory ){
		$this->queueFactory = $factory;
	}

} 