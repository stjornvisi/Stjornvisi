<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 28/09/14
 * Time: 21:53
 */

namespace Stjornvisi\Notify;

use Psr\Log\LoggerInterface;

class Null implements NotifyInterface {

	/**
	 * @param LoggerInterface $logger
	 * @return $this|NotifyInterface
	 */
	public function setLogger(LoggerInterface $logger){
		return $this;
	}

	/**
	 * @param $data
	 * @return $this|NotifyInterface
	 */
	public function setData( $data ){
		return $this;
	}

	/**
	 * @return $this|NotifyInterface
	 */
	public function send(){
		return $this;
	}
} 