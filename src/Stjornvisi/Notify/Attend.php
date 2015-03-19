<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Psr\Log\LoggerInterface;
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

	/**
	 * @var \stdClass
	 */
	private $params;

	/**
	 * @var  \Psr\Log\LoggerInterface;
	 */
	private $logger;

	/**
	 * @var \Stjornvisi\Service\User
	 */
	private $user;

	/**
	 * @var \Stjornvisi\Service\Event
	 */
	private $event;

	/**
	 * @var \Stjornvisi\Lib\QueueConnectionFactoryInterface
	 */
	private $queueFactory;

	/**
	 * @var array
	 */
	private $config;

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

		$this->user->validateConnection();
		$this->event->validateConnection();

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
		//	create and configure view
		$child = new ViewModel(array(
			'user' => $userObject,
			'event' => $eventObject
		));
		$child->setTemplate(($this->params->data->type)?'attend':'unattend');

		$layout = new ViewModel();
		$layout->setTemplate('layout');
		$layout->addChild($child, 'content');

		$phpRenderer = new \Zend\View\Renderer\PhpRenderer();
		$phpRenderer->setCanRenderTrees(true);

		$resolver = new \Zend\View\Resolver\TemplateMapResolver();
		$resolver->setMap(array(
			'layout' => __DIR__ . '/../../../view/layout/email.phtml',
			'attend' => __DIR__ . '/../../../view/email/attending.phtml',
			'unattend' => __DIR__ . '/../../../view/email/un-attending.phtml',
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
			'recipient' => array('name'=>$userObject->name, 'address'=>$userObject->email),
			'subject' => ($this->params->data->type)
					? "Þú hefur skráð þig á viðburðinn: {$eventObject->subject}"
					: "Þú hefur afskráð þig á viðburðinn: {$eventObject->subject}",
			'body' => $phpRenderer->render($layout),
			'id' => '',
			'user_id' => 0,
			'type' => '',
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
			$this->logger->info( get_class($this) .":send".
				" {$result['recipient']['address']} is " . ( ($this->params->data->type)?'':'not ' ) .
				"attending {$eventObject->subject}");
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
	 * @param QueueConnectionFactoryInterface $factory
	 * @return Attend
	 */
	public function setQueueConnectionFactory( QueueConnectionFactoryInterface $factory ){
		$this->queueFactory = $factory;
		return $this;
	}
}