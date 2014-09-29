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

use Zend\Log\LoggerInterface;

/**
 * Handler for when a user registers / un-registers to a group.
 *
 * @package Stjornvisi\Notify
 */
class Submission implements NotifyInterface {

	const REGISTER = 'user.register/un-register.with.group';

	/** @var \stdClass */
	private $params;

	/** @var \Stjornvisi\Service\Group */
	private $group;

	/** @var \Stjornvisi\Service\User */
	private $user;

	/** @var  \Zend\Log\LoggerInterface */
	private $logger;

	/**
	 * @param UserService $userService
	 * @param GroupService $groupService
	 */
	public function __construct( UserService $userService, GroupService $groupService ){
		$this->user = $userService;
		$this->group = $groupService;
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