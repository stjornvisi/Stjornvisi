<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Lib\QueueConnectionFactoryInterface;
use Stjornvisi\Lib\QueueConnectionAwareInterface;

use Stjornvisi\Service\User;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Psr\Log\LoggerInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Handler for sending e-mail to everyone
 *
 * This will transcend all Group config. Only
 * Admin can do this.
 *
 * @package Stjornvisi\Notify
 */
class All implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface {

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
	private $userDAO;

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
	 * The the data to be passed to the mail process.
	 *
	 * @param $data {
	 * 	@group_id: int
	 *  @recipients: allir|formenn
	 * 	@test: bool
	 *  @subject: string
	 * 	@body: string
	 * 	@sender_id: int
	 * }
	 * @return $this|NotifyInterface
	 */
	public function setData( $data ){
		$this->params = $data->data;
		return $this;
	}

	/**
	 * Set a logger object to monitor the handler.
	 *
	 * @param LoggerInterface $logger
	 * @return $this|NotifyInterface
	 */
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Run the handler.
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

		$this->userDAO = new User();
		$this->userDAO->setDataSource( $pdo )
			->setEventManager( $this->getEventManager() );

		$emailId = md5( time() + rand(0,1000) );

		//TEST OR REAL
		//	if test, send ony to sender, else to all
		$users = ($this->params->test)
			? array( $this->userDAO->get( $this->params->sender_id ) )
			:  (($this->params->recipients == 'allir')
				? $this->userDAO->fetchAll(true)
				:  $this->userDAO->fetchAllLeaders(true)) ;

		$this->logger->info(
			get_class($this) . " sending email to [{$this->params->recipients}] in" .
			( $this->params->test ? ' ' : 'non-' ) . "test mode");

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
				'script' => __DIR__ . '/../../../view/email/letter.phtml',
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
					'body' => $phpRenderer->render($layout),
					'id' => $emailId,
					'user_id' => md5( (string)$emailId . $user->email  ),
					'entity_id' => null,
					'type' => 'All',
					'parameters' => $this->params->recipients,
					'test' => $this->params->test
				);

				$msg = new AMQPMessage( json_encode($result),
					array('delivery_mode' => 2) # make message persistent
				);

				$this->logger->info(get_class($this)." sending mail to user:{$user->email}");

				$channel->basic_publish($msg, '', 'mail_queue');
			}

		}catch (\Exception $e){
			while($e){
				$this->logger->critical(
					get_class($this) . ":send says: {$e->getMessage()}",
					$e->getTrace()
				);
				$e = $e->getPrevious();
			}

		}finally{
			if( $channel ){
				$channel->close();
			}
			if( $connection ){
				$connection->close();
			}

			$pdo = null;
			$this->userDAO = null;
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