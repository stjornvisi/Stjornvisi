<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/01/15
 * Time: 15:31
 */

namespace Stjornvisi\Notify;


use Stjornvisi\Service\User;
use Psr\Log\LoggerInterface;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Facebbok OAuth URL sent to user in an e-mail.
 *
 * @package Stjornvisi\Notify
 */
class UserValidate implements NotifyInterface, QueueConnectionAwareInterface {

	/**
	 * @var  \Psr\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * @var \stdClass
	 */
	private $params;

	/**
	 * @var \Stjornvisi\Lib\QueueConnectionFactoryInterface
	 */
	private $queueFactory;

	/**
	 * @var \Stjornvisi\Service\User
	 */
	private $user;

	/**
	 * @var array
	 */
	private $config;

	/**
	 * @param User $user
	 */
	public function __construct( User $user ){
		$this->user = $user;
	}

	/**
	 * Set logger to monitor.
	 *
	 * @param LoggerInterface $logger
	 * @return $this|NotifyInterface
	 */
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Set the data that is coming from the
	 * producer.
	 *
	 * @param $data {
	 *	@user_id: int
	 *	@url: string
	 *	@facebook: string
	 * }
	 * @return $this|NotifyInterface
	 */
	public function setData( $data ){
		$this->params = $data->data;
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

		//USER
		//	get the user.
		$user = $this->user->get( $this->params->user_id );


		//VIEW
		//	create and configure view
		$child = new ViewModel(array(
			'user' => $user,
			'link' => $this->params->facebook
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
			'script' => __DIR__ . '/../../../view/email/user-validate.phtml',
		));
		$phpRenderer->setResolver($resolver);

		//MAIL
		//	now we want to send this to the user/quest via e-mail
		//	so we try to connect to Queue and send a message
		//	to mail_queue
		try{
			$connection = $this->queueFactory->createConnection();
			$channel = $connection->channel();
			$channel->queue_declare('mail_queue', false, true, false, false);

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
				'subject' => "Stjórnvísi, staðfesting á aðgangi",
				'body' => $phpRenderer->render($layout),
				'user_id' => null,
				'type' => '',
				'entity_id' => null,
				'parameters' => '',
				'test' => true
			);
			$msg = new AMQPMessage( json_encode($result),
				array('delivery_mode' => 2) # make message persistent
			);

			$this->logger->info("User validate email to [{$user->email}]");

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