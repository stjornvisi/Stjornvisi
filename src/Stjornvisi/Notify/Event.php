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

/**
 * Class Event
 * @package Stjornvisi\Notify
 */
class Event implements NotifyInterface {

	const MESSAGING = 'messaging all in event';

	/** @var \stdClass */
	private $params;

	/** @var \Stjornvisi\Service\Event */
	private $event;

	/** @var \Stjornvisi\Service\User */
	private $user;

	/** @var  \Zend\Log\LoggerInterface */
	private $logger;

	/**
	 * @param UserService $userService
	 * @param EventService $eventService
	 */
	public function __construct( UserService $userService, EventService $eventService ){
		$this->user = $userService;
		$this->event = $eventService;
	}

	public function setData( $data ){
		$this->params = $data;
	}
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
	}

	public function send(){
		$this->logger->info("---Now I need to aggregate who will get the message");
		$this->logger->info(__NAMESPACE__ . get_class($this).__FUNCTION__);
		$this->logger->info(print_r($this->params,true));
		$this->logger->info("--Aggregate done");
	}

} 