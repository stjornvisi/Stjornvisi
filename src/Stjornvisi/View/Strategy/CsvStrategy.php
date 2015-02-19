<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 15/02/15
 * Time: 14:39
 */

namespace Stjornvisi\View\Strategy;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model;
use Zend\View\Renderer\JsonRenderer;
use Zend\View\ViewEvent;

use Zend\View\Renderer\RendererInterface;

class CsvStrategy extends AbstractListenerAggregate {
	protected $renderer;
	protected $listeners = array();

	public function __construct(RendererInterface $renderer)
	{
		$this->renderer = $renderer;
	}

	public function attach(EventManagerInterface $events, $priority = 1)
	{
		$this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
		$this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
	}

	public function selectRenderer(ViewEvent $e)
	{
		$model = $e->getModel();

		if (!$model instanceof \Stjornvisi\View\Model\CsvModel) {
			// no JsonModel; do nothing
			return;
		}

		// JsonModel found
		return $this->renderer;

	}

	public function injectResponse(ViewEvent $e)
	{

		$model = $e->getModel();
		if (!$model instanceof \Stjornvisi\View\Model\CsvModel) {
			// no JsonModel; do nothing
			return;
		}
		$csv = $model->getData();


		$result   = $e->getResult();

		// Populate response
		$response = $e->getResponse();
		$response->setContent($result);
		$headers = $response->getHeaders();


		$headers
			->addHeaderLine('content-type', 'text/csv; charset=utf-8')
			->addHeaderLine('Content-Disposition',
				sprintf("attachment; filename=\"%s\"",$csv->getName())
			);


	}

	public function detach(EventManagerInterface $events)
	{
		foreach ($this->listeners as $index => $listener) {
			if ($events->detach($listener)) {
				unset($this->listeners[$index]);
			}
		}
	}
} 