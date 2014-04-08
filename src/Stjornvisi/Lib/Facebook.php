<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 25/03/14
 * Time: 10:15
 */

namespace Stjornvisi\Lib;

use \Facebok;

class Facebook extends \Facebook{

	private $redirect_uri = '';

	public function __construct($config){
		parent::__construct($config);
		$this->setRedirectUri( $config['redirect_uri'] );
	}
	public function getLoginUrl( $params=array() ){
		$params = array_merge(
			$params,
			array('redirect_uri'=>$this->redirect_uri)
		);
		return parent::getLoginUrl($params);
	}
	public function setRedirectUri( $uri ){
		$this->redirect_uri = $uri;
	}
} 
