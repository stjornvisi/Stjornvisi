<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 13/03/14
 * Time: 16:09
 */

namespace Stjornvisi\Form\Element;

use \Zend\Form\Element\Text;

class Avatar extends Text{
	/**
	 * @var array
	 */
	protected $attributes = array(
		'type' => 'text',
		'data-type' => 'avatar'
	);
} 
