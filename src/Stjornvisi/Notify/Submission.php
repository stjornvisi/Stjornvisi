<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Service\User as UserService;
use Stjornvisi\Service\Group as GroupService;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Psr\Log\LoggerInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Handler for when a user registers / un-registers to a group.
 *
 * @package Stjornvisi\Notify
 */
class Submission implements NotifyInterface, QueueConnectionAwareInterface {

	/** @var \stdClass */
	private $params;

	/** @var \Stjornvisi\Service\Group */
	private $group;

	/** @var \Stjornvisi\Service\User */
	private $user;

	/** @var  \Psr\Log\LoggerInterface */
	private $logger;

	/** @var \Stjornvisi\Lib\QueueConnectionFactoryInterface  */
	private $queueFactory;

	/**
	 * @param UserService $userService
	 * @param GroupService $groupService
	 */
	public function __construct( UserService $userService, GroupService $groupService ){
		$this->user = $userService;
		$this->group = $groupService;
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

		//VALUE OBJECTS
		//	use the services to get the values objects needed.
		$groupObject = $this->group->get( $this->params->data->group_id );
		$userObject = $this->user->get( $this->params->data->recipient );


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
			'group' => $groupObject
		));
		$resolver->attach($map)->attach($stack);


		//TEMPLATE AND DATA
		//	select the correct template to render
		//	and fill data object with correct data.
		if($this->params->data->register){
			$model->setTemplate('group-register');
			$result = array(
				'recipient' => array('name'=>$userObject->name, 'address'=>$userObject->email),
				'subject' => "Þú hefur skráð þig í hópinn: {$groupObject->name}",
				'body' => $renderer->render($model)
			);
		}else{
			$model->setTemplate('group-unregister');
			$result = array(
				'recipient' => array('name'=>$userObject->name, 'address'=>$userObject->email),
				'subject' => "Þú hefur afskráð þig úr hópnum: {$groupObject->name}",
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
				" {$result['recipient']['address']} is " . ( ($this->params->data->register)?'':'not ' ) .
				"joining group {$groupObject->name_short}");
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