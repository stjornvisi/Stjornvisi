<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Psr\Log\LoggerInterface;

/**
 * Handler for when a user registers / un-registers to a group.
 *
 * @package Stjornvisi\Notify
 */
class Password implements NotifyInterface {

	/** @var \stdClass */
	private $params;

	/** @var  \Psr\Log\LoggerInterface */
	private $logger;


	public function __construct( ){

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