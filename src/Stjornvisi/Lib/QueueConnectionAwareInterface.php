<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 09/10/14
 * Time: 23:31
 */

namespace Stjornvisi\Lib;

/**
 * Interface for objects that need to connect to Queue.
 *
 * Interface QueueConnectionAwareInterface
 * @package Stjornvisi\Lib
 */
interface QueueConnectionAwareInterface {

	/**
	 * Set Queue factory
	 * @param QueueConnectionFactoryInterface $factory
	 * @return mixed
	 */
	public function setQueueConnectionFactory( QueueConnectionFactoryInterface $factory );
} 