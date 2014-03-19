<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/23/14
 * Time: 1:36 PM
 */

namespace Stjornvisi\View\Strategy;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\View\ViewEvent;

use Stjornvisi\View\Model\CsvModel;

class CsvStrategy implements ListenerAggregateInterface {
    protected $renderer;
    protected $listeners = array();

    public function __construct(RendererInterface $renderer){
        $this->renderer = $renderer;
    }

    public function selectRenderer(ViewEvent $e){
        $model = $e->getModel();

        if (!$model instanceof CsvModel) {
            // no JsonModel; do nothing
            return;
        }

        // CsvModel found
        return $this->renderer;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     * @param int $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1){
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
    }

    public function injectResponse(ViewEvent $e){
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            return;
        }

        $result   = $e->getResult();

        $response = $e->getResponse();
        $response->setContent($result);
        $headers = $response->getHeaders();
        $headers->addHeaderLine('content-type', 'text/csv');
    }

    /**
     * Detach all previously attached listeners
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }
}