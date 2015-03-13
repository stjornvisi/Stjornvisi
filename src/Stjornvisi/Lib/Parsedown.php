<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 13/03/15
 * Time: 17:00
 */

namespace Stjornvisi\Lib;

use \Parsedown as Markdown;

class Parsedown extends Markdown{

	protected $BlockTypes = array(
		'#' => array('Header'),
		'*' => array('Rule', 'List'),
		'+' => array('List'),
		'-' => array('SetextHeader', 'Table', 'Rule', 'List'),
		':' => array('Table'),
		'<' => array('Comment', 'Markup'),
		'=' => array('SetextHeader'),
		'>' => array('Quote'),
		'[' => array('Reference'),
		'_' => array('Rule'),
		'`' => array('FencedCode'),
		'|' => array('Table'),
		'~' => array('FencedCode'),
	);

	protected $breaksEnabled = true;

} 