<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 18/03/15
 * Time: 22:26
 */

namespace Stjornvisi\Lib;

use \PDO as OriginalPDO;

/**
 * Class PDO
 * @package Stjornvisi\Lib
 * @deprecated
 */
class PDO extends OriginalPDO {

	protected $dsn;
	protected $username;
	protected $password;
	protected $options;

	public function __construct( $dsn,  $username = "",  $password = "", array $options = array() ){
		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
		$this->options = $options;
		parent::__construct( $dsn,  $username,  $password,  $options );
	}

	public function getDsn(){
		return $this->dsn;
	}

	public function getUsername(){
		return $this->username;
	}

	public function getPassword(){
		return $this->password;
	}

	public function getOptions(){
		return $this->options;
	}
} 