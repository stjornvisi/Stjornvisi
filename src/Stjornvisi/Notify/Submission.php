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

	/**
	 * @var \stdClass
	 */
	private $params;

	/**
	 * @var \Stjornvisi\Service\Group
	 */
	private $group;

	/**
	 * @var \Stjornvisi\Service\User
	 */
	private $user;

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
		$this->group->validateConnection();

		//VALUE OBJECTS
		//	use the services to get the values objects needed.
		$groupObject = $this->group->get( $this->params->data->group_id );
		$userObject = $this->user->get( $this->params->data->recipient );


		//VIEW
		//	create and configure view
		$child = new ViewModel(array(
			'user' => $userObject,
			'group' => $groupObject
		));
		$child->setTemplate( ($this->params->data->register)
			? 'group-register'
			: 'group-unregister');

		$layout = new ViewModel();
		$layout->setTemplate('layout');
		$layout->addChild($child, 'content');

		$phpRenderer = new \Zend\View\Renderer\PhpRenderer();
		$phpRenderer->setCanRenderTrees(true);

		$resolver = new \Zend\View\Resolver\TemplateMapResolver();
		$resolver->setMap(array(
			'layout' => __DIR__ . '/../../../view/layout/email.phtml',
			'group-register' => __DIR__ . '/../../../view/email/group-register.phtml',
			'group-unregister' => __DIR__ . '/../../../view/email/group-unregister.phtml',
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

		$result = array(
			'recipient' => array('name'=>$userObject->name, 'address'=>$userObject->email),
			'subject' => ($this->params->data->register)
					? "Þú hefur skráð þig í hópinn: {$groupObject->name}"
					: "Þú hefur afskráð þig úr hópnum: {$groupObject->name}",
			'body' => $phpRenderer->render($layout)
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

		return $this;
	}

	/**
	 * Set Queue factory
	 * @param QueueConnectionFactoryInterface $factory
	 * @return $this|NotifyInterface
	 */
	public function setQueueConnectionFactory( QueueConnectionFactoryInterface $factory ){
		$this->queueFactory = $factory;
		return $this;
	}
} 