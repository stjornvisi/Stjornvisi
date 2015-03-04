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

class UserValidate implements NotifyInterface, QueueConnectionAwareInterface {

	/** @var  \Psr\Log\LoggerInterface */
	private $logger;

	/** @var \stdClass */
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
	 * @param User $user
	 */
	public function __construct( User $user ){
		$this->user = $user;
	}

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
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
	 * @return mixed
	 */
	public function setData( $data ){
		$this->params = $data->data;
	}

	/**
	 * Send notification to what ever media or outlet
	 * required by the implementer.
	 *
	 * @return mixed
	 */
	public function send(){

		//USER
		//	get the user.
		$user = $this->user->get( $this->params->user_id );

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
			'user' => $user,
			'link' => $this->params->facebook
		));
		$resolver->attach($map)->attach($stack);
		$model->setTemplate('user-validate');

		$this->logger->info("User validate");

		//MAIL
		//	now we want to send this to the user/quest via e-mail
		//	so we try to connect to Queue and send a message
		//	to mail_queue
		try{
			$connection = $this->queueFactory->createConnection();
			$channel = $connection->channel();
			$channel->queue_declare('mail_queue', false, true, false, false);


			$result = array(
				'recipient' => array('name'=>$user->name, 'address'=>$user->email),
				'subject' => "Stjórnvísi, staðfesting á aðgangi",
				'body' => $renderer->render($model)
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