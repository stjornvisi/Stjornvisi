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


	protected function blockList($Line)
	{
		list($name, $pattern) = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ul', '[*+-]');

		if (preg_match('/^('.$pattern.'[ ]+)(.*)/', $Line['text'], $matches))
		{
			$Block = array(
				'indent' => $Line['indent'],
				'pattern' => $pattern,
				'element' => array(
					'name' => $name,
					'handler' => 'elements',
				),
			);

			$Block['li'] = array(
				'name' => 'li',
				'handler' => 'li',
				'text' => array(
					$matches[2],
				),
			);

			$Block['element']['text'] []= & $Block['li'];

			return $Block;
		}
	}

	protected function blockListContinue($Line, array $Block)
	{
		if ($Block['indent'] === $Line['indent'] and preg_match('/^'.$Block['pattern'].'(?:[ ]+(.*)|$)/', $Line['text'], $matches))
		{
			if (isset($Block['interrupted']))
			{
				$Block['li']['text'] []= '';

				unset($Block['interrupted']);
			}

			unset($Block['li']);

			$text = isset($matches[1]) ? $matches[1] : '';

			$Block['li'] = array(
				'name' => 'li',
				'handler' => 'li',
				'text' => array(
					$text,
				),
			);

			$Block['element']['text'] []= & $Block['li'];

			return $Block;
		}

		if ($Line['text'][0] === '[' and $this->blockReference($Line))
		{
			return $Block;
		}

		if ( ! isset($Block['interrupted']))
		{
			$text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);

			$Block['li']['text'] []= $text;

			return $Block;
		}

		if ($Line['indent'] > 0)
		{
			$Block['li']['text'] []= '';

			$text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);

			$Block['li']['text'] []= $text;

			unset($Block['interrupted']);

			return $Block;
		}
	}
} 