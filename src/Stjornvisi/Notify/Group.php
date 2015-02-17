<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Service\User as UserDAO;
use Stjornvisi\Service\Group as GroupDAO;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\Log\LoggerInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Handler for when a user registers / un-registers to a group.
 *
 * @package Stjornvisi\Notify
 */
class Group implements NotifyInterface {

	/** @var \stdClass */
	private $params;

	/** @var  \Zend\Log\LoggerInterface */
	private $logger;

	/**
	 * @var \Stjornvisi\Service\User
	 */
	private $userDAO;

	/**
	 * @var \Stjornvisi\Service\Group
	 */
	private $groupDAO;

	/**
	 * @var \Stjornvisi\Lib\QueueConnectionFactoryInterface
	 */
	private $queueFactory;

	/**
	 * @param UserDAO $user
	 * @param GroupDAO $group
	 */
	public function __construct( UserDAO $user, GroupDAO $group ){
		$this->userDAO = $user;
		$this->groupDAO = $group;
	}

	/**
	 * @param $data {
	 * 	@group_id: int
	 *  @recipients: allir|formenn
	 * 	@test: bool
	 *  @subject: string
	 * 	@body: string
	 * 	@sender_id: int
	 * }
	 * @return mixed|void
	 */
	public function setData( $data ){
		$this->params = $data->data;
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
	}

	/**
	 * @return mixed|void
	 *
	 */
	public function send(){

		//ALL OR FORMEN
		//	send to all members of group or forman
		$exclude = ( $this->params->recipients == 'allir' )
			? array(-1) 	//everyone
			: array(0) ; 	//forman

		//TEST OR REAL
		//	if test, send ony to sender, else to all
		$users = ($this->params->test)
			? array( $this->userDAO->get( $this->params->sender_id ) )
			: $this->userDAO->getUserMessageByGroup(array($this->params->group_id),$exclude);

		//GROUP
		//	get the group object
		$group = $this->groupDAO->get( $this->params->group_id );

		$this->logger->info("Group-email in " . ( $this->params->test?'':'none' ) . " test mode");

		//MAIL
		//	now we want to send this to the user/quest via e-mail
		//	so we try to connect to Queue and send a message
		//	to mail_queue
		try{

			//QUEUE
			//	create and configure queue
			$connection = $this->queueFactory->createConnection();
			$channel = $connection->channel();
			$channel->queue_declare('mail_queue', false, true, false, false);

			$paragrapher = new \Stjornvisi\View\Helper\Paragrapher();

			//VIEW
			//	create and configure view
			$child = new ViewModel(array(
				'user' => null,
				'group' => $group,
				'body' => $paragrapher->__invoke($this->params->body)
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
				'script' => __DIR__ . '/../../../view/email/group-letter.phtml',
			));
			$phpRenderer->setResolver($resolver);

			//FOR EVERY USER
			//	for every user, render mail-template
			//	and send to mail-queue
			foreach($users as $user){

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
					'subject' => $this->params->subject,
					'body' => $phpRenderer->render($layout)
				);

				$msg = new AMQPMessage( json_encode($result),
					array('delivery_mode' => 2) # make message persistent
				);

				$this->logger->info("Groupmail to user:{$user->email}, group:{$group->name_short}");

				$channel->basic_publish($msg, '', 'mail_queue');
			}

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