<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/31/14
 * Time: 9:35 PM
 */

namespace Stjornvisi\Event;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;

/**
 * This listener is listening for EVENT_DISPATCH event.
 * It will then select the correct layout.
 *
 * Class ActivityListener
 * @package Stjornvisi\Event
 */
class LayoutSelectListener extends AbstractListenerAggregate
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
        $this->listeners[] = $events->attach([MvcEvent::EVENT_DISPATCH], [$this, 'dispatch'], -100);
    }

    public function dispatch(MvcEvent $event)
    {
        $auth = new AuthenticationService();
        if (!$auth->hasIdentity()) {
            $router = $event->getRouteMatch();
            if (method_exists($router, 'getMatchedRouteName') && $router->getMatchedRouteName() == 'home') {
                $event->getViewModel()->setTemplate('layout/landing');
            } else {
                $event->getViewModel()->setTemplate('layout/anonymous');
            }
        }
    }
}
