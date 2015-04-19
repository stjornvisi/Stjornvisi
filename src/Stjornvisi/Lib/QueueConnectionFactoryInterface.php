<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 09/10/14
 * Time: 23:34
 */

namespace Stjornvisi\Lib;

interface QueueConnectionFactoryInterface
{
	public function setConfig(array $config);

	/**
	 * @return \PhpAmqpLib\Connection\AMQPConnection
	 */
	public function createConnection();
}
