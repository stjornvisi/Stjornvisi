<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Zend\Log\LoggerInterface;
use Stjornvisi\Service\User as UserDAO;
use Stjornvisi\Service\Event as EventDAO;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;

use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Handler to send attendance message to users after they
 * have registered to event.
 *
 * @package Stjornvisi\Notify
 */
class Attend implements NotifyInterface, QueueConnectionAwareInterface {

	/** @var \stdClass */
	private $params;

	/** @var  \Zend\Log\LoggerInterface */
	private $logger;

	/** @var \Stjornvisi\Service\User  */
	private $user;

	/** @var \Stjornvisi\Service\Event */
	private $event;

	/** @var \Stjornvisi\Lib\QueueConnectionFactoryInterface  */
	private $queueFactory;


	/**
	 * Create an instance to this handler.
	 * It requires two services.
	 *
	 * @param UserDAO $user
	 * @param EventDAO $event
	 */
	public function __construct( UserDAO $user, EventDAO $event ){
		$this->user = $user;
		$this->event = $event;
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

		//USER
		//	user can be in the system or he can be
		//	a guest, we have to prepare for both.
		if( is_numeric($this->params->data->recipients) ){
			$userObject = $this->user->get($this->params->data->recipients);
		}else{
			$userObject = (object)array(
				'name' => $this->params->data->recipients->name,
				'email' => $this->params->data->recipients->email
			);
		}

		//EVENT
		//	now we need the event that the user/guest
		//	is registering to.
		$eventObject = $this->event->get( $this->params->data->event_id );

		//VIEW
		//	create and config template/rendering engine
		// 	and model and mash it together.
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

		$model = new ViewModel(array(
			'user' => $userObject,
			'event' => $eventObject
		));
		$resolver->attach($map)->attach($stack);


		//ATTEND / UN-ATTEND
		//	check if the user is registering
		//	or un-registering and render template to
		//	accommodate.
		if($this->params->data->type){
			$model->setTemplate('attending');
			$result = array(
				'recipient' => array('name'=>$userObject->name, 'address'=>$userObject->email),
				'subject' => "Þú hefur skráð þig á viðburðinn: {$eventObject->subject}",
				'body' => $renderer->render($model)
			);
		}else{
			$model->setTemplate('un-attending');
			$result = array(
				'recipient' => array('name'=>$userObject->name, 'address'=>$userObject->email),
				'subject' => "Þú hefur afskráð þig á viðburðinn: {$eventObject->subject}",
				'body' => $renderer->render($model)
			);
		}

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
			$this->logger->info( get_class($this) .":send".
				" {$result['recipient']['address']} is " . ( ($this->params->data->type)?'':'not ' ) .
				"attending {$eventObject->subject}");
			$channel->basic_publish($msg, '', 'mail_queue');


		}catch (\Exception $e){
			$this->logger->warn(get_class($this) . ":send says: {$e->getMessage()}");
		}finally{
			if( $channel ){
				$channel->close();
			}
			if( $connection ){
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