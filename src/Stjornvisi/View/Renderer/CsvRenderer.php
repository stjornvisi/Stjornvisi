<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 15/02/15
 * Time: 14:48
 */

namespace Stjornvisi\View\Renderer;

use Zend\View\Model\ModelInterface;
use Zend\View\Renderer\RendererInterface as Renderer;
use Zend\View\Renderer\TreeRendererInterface;
use Zend\View\Resolver\ResolverInterface;

class CsvRenderer implements Renderer, TreeRendererInterface
{
	/**
	 * Return the template engine object, if any
	 *
	 * If using a third-party template engine, such as Smarty, patTemplate,
	 * phplib, etc, return the template engine object. Useful for calling
	 * methods on these objects, such as for setting filters, modifiers, etc.
	 *
	 * @return mixed
	 */
	public function getEngine()
	{
		return $this;
	}

	/**
	 * Set the resolver used to map a template name to a resource the renderer may consume.
	 *
	 * @param  ResolverInterface $resolver
	 * @return Renderer
	 */
	public function setResolver(ResolverInterface $resolver)
	{
		$this->resolver = $resolver;
	}

	/**
	 * Processes a view script and returns the output.
	 *
	 * @param  string|ModelInterface $nameOrModel The script/resource process, or a view model
	 * @param  null|array|\ArrayAccess $values Values to use during rendering
	 * @return string The script output.
	 */
	public function render($nameOrModel, $values = null){

		/** @var  $nameOrModel \Stjornvisi\View\Model\CsvModel */
		$csv = $nameOrModel->getData();
		/** @var $csv \Stjornvisi\Lib\Csv */
		$string = implode(",", array_map(function($data){
			return "\"".addslashes($data)."\"";
		},$csv->getHeader())) . PHP_EOL;

		foreach( $csv as $item ){
			$string .= implode(",", array_map(function($data){
				return "\"".addslashes($data)."\"";
			},$item));
			$string .= PHP_EOL;
		}
		return $string;

	}

	/**
	 * Indicate whether the renderer is capable of rendering trees of view models
	 *
	 * @return bool
	 */
	public function canRenderTrees()
	{
		return true;
	}

}