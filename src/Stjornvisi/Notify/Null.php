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

	public function setLogger(LoggerInterface $logger){}
	public function setData( $data ){}
	public function send(){}
} 