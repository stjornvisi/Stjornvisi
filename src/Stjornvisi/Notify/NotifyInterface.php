<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:33
 */

namespace Stjornvisi\Notify;

use Psr\Log\LoggerInterface;

interface NotifyInterface {

	/**
	 * Set the data that is coming from the
	 * producer.
	 *
	 * @param $data
	 * @return mixed
	 */
	public function setData( $data );

	/**
	 * Send notification to what ever media or outlet
	 * required by the implementer.
	 *
	 * @return mixed
	 */
	public function send();

	/**
	 * Set logger instance
	 *
	 * @param \Psr\Log\LoggerInterface
	 */
	public function setLogger(LoggerInterface $logger);
}
