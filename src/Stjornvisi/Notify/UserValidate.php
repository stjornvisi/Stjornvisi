<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/01/15
 * Time: 15:31
 */

namespace Stjornvisi\Notify;


use Zend\Log\LoggerInterface;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

class UserValidate implements NotifyInterface, QueueConnectionAwareInterface {

	/** @var  \Zend\Log\LoggerInterface */
	private $logger;

	/** @var \stdClass */
	private $params;

	/**
	 * Set logger instance
	 *
	 * @param $logger LoggerInterface
	 */
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
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
	 * Send notification to what ever media or outlet
	 * required by the implementer.
	 *
	 * @return mixed
	 */
	public function send()
	{
		$this->logger->debug( print_r($this->params,true) );
		// TODO: Implement send() method.
	}

	public function setQueueConnectionFactory(QueueConnectionFactoryInterface $factory)
	{
		// TODO: Implement setQueueConnectionFactory() method.
	}
}