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

class IcalRenderer implements Renderer, TreeRendererInterface {

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
	public function render($nameOrModel, $values = null)
	{
		$string = "BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:-//hacksw/handcal//NONSGML v1.0//EN\n";

		foreach( $nameOrModel->getVariable('events') as $event ){

			$string .= "BEGIN:VEVENT\n";
			$string .= "UID:{$event->id}@stjornvisi.is\n";
			$string .= "DTSTART:{$event->event_time->format('Ymd\THis')}\n";
			$string .= "DTEND:{$event->event_end->format('Ymd\THis')}\n";
			if($event->lat && $event->lng){
				$string .= "GEO:{$event->lat};{$event->lng}\n";
			}
			$string .= "LOCATION:{$event->location}\n";
			$string .= "ORGANIZER;CN=\"". (($event->groups) ? (implode(', ',array_map(function($g){
					return $g->name;
				},$event->groups))):'Stjónvísisviðburður') ."\":no-reply@stjornvisi.is\n";

			$string .= "LOCATION:{$event->location}\n";
			$string .= "URL:http://{$_SERVER['SERVER_NAME']}/vidburdir/{$event->id}\n";

			$string .= "SUMMARY:{$event->subject}\n";
			$string .= "END:VEVENT\n";
		}

		$string .= "END:VCALENDAR";

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