<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/10/14
 * Time: 4:47 PM
 */

namespace Stjornvisi\View\Helper;

use Zend\View\Helper\AbstractHelper;
use \Parsedown;

class Paragrapher extends AbstractHelper{

	private static $parser;

    public function __invoke($value){
		if( $value=='' ){
            return '';
        }
		//SINGLE INSTANCE
		//	only create one instance of Parser
		if( !self::$parser ){
			self::$parser = new Parsedown();
		}
		return self::$parser->text($value);
    }
} 