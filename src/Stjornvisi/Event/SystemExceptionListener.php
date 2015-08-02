<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 5/04/15
 * Time: 9:22 PM
 */

namespace Stjornvisi\Event;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;

class SystemExceptionListener extends AbstractListenerAggregate implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach([MvcEvent::EVENT_DISPATCH_ERROR], [$this, 'dispatchError']);
        $this->listeners[] = $events->attach([MvcEvent::EVENT_RENDER_ERROR], [$this, 'renderError']);
    }

    /**
     * @param MvcEvent $event
     */
    public function dispatchError(MvcEvent $event)
    {
        $exception = $event->getParam('exception');
        while ($exception) {
            $this->logger->critical('EVENT_DISPATCH_ERROR: '.$exception->getMessage(), $exception->getTrace());
            $exception = $exception->getPrevious();
        }
        if (($event->isError() ) == true && $event->getError() == Application::ERROR_EXCEPTION) {
            $this->logger->critical('EVENT_DISPATCH_ERROR: '.$event->getError());
        }
    }

    /**
     * @param MvcEvent $event
     */
    public function renderError(MvcEvent $event)
    {
        $exception = $event->getParam('exception');
        /** @var $exception \Zend\Mvc\Router\Exception\InvalidArgumentException */

        $request = $event->getRequest();
        /** @var  $request \Zend\Http\PhpEnvironment\Request */

        while ($exception) {
            $this->logger->critical('EVENT_RENDER_ERROR: ' . $exception->getMessage() . " in path [{$request->getUriString()}]", $exception->getTrace());
            $exception = $exception->getPrevious();
        }
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
