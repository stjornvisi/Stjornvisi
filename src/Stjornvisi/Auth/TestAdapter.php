<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 11/11/14
 * Time: 12:41
 */

namespace Stjornvisi\Auth;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class TestAdapter implements AdapterInterface {

	private $user;

	public function __construct( $user ){
		$this->user = $user;
	}

	/**
	 * Performs an authentication attempt
	 *
	 * @return \Zend\Authentication\Result
	 * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
	 */
	public function authenticate(){
		return new Result( Result::SUCCESS, $this->user );
	}
} 