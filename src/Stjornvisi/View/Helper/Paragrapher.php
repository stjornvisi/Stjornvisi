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
		/*
        $value = trim($value);
        $txt = preg_replace(
            '/(?<!S)((http(s?):\/\/)|(www.))+([\w.1-9\&=#?\-~%;\/]+)/',
            '<a href="http$3://$4$5" target="_blank">http$3://$4$5</a>', $value);

        //Basic formatting
        $eol = ( strpos($txt,"\r") === FALSE ) ? "\n" : "\r\n";
        $html = '<p>'.str_replace("$eol$eol","</p><p>",$txt).'</p>';
        $html = str_replace("$eol","<br />\n",$html);
        $html = str_replace("</p>","</p>\n\n",$html);
        $html = str_replace("<p></p>","<p>&nbsp;</p>",$html);
        return $html;
		*/
    }
} 