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

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Handler to send attendance message to users after they
 * have registered to event.
 *
 * @package Stjornvisi\Notify
 */
class Attend implements NotifyInterface, QueueConnectionAwareInterface {

	const ATTENDING = 'attending.event';

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


	public function __construct( UserDAO $user, EventDAO $event ){
		$this->user = $user;
		$this->event = $event;
	}

	public function setData( $data ){
		$this->params = $data;
	}
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
	}

	public function send(){

		if( is_numeric($this->params->data->recipients) ){
			$userObject = $this->user->get($this->params->data->recipients);
		}else{
			$userObject = (object)array(
				'name' => $this->params->data->recipients->name,
				'email' => $this->params->data->recipients->email
			);
		}

		$eventObject = $this->event->get( $this->params->data->event_id );

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


		try{
			$connection = $this->queueFactory->createConnection();
			$channel = $connection->channel();

			$channel->queue_declare('mail_queue', false, true, false, false);
			$msg = new AMQPMessage( json_encode($result),
				array('delivery_mode' => 2) # make message persistent
			);

			$channel->basic_publish($msg, '', 'mail_queue');


		}catch (\Exception $e){
			$this->logger->warn("Mail Queue Service says: {$e->getMessage()}");
		}finally{
			$channel->close();
			$connection->close();
		}

		$this->logger->info(print_r($result,true));
		$this->logger->info(print_r($this->params->data,true));
	}

	public function setQueueConnectionFactory( QueueConnectionFactoryInterface $factory ){
		$this->queueFactory = $factory;
	}
}