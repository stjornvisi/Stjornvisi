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
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\SessionManager;
use \Zend\Console\Request as ConsoleRequest;

/**
 * Class ActivityListener
 * @package Stjornvisi\Event
 * @todo major refactoring
 */
class PersistenceLoginListener extends AbstractListenerAggregate implements LoggerAwareInterface, ServiceLocatorAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $serviceLocator;

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
        $this->listeners[] = $events->attach([MvcEvent::EVENT_DISPATCH], [$this, 'dispatch'], 100);
    }

    public function dispatch(MvcEvent $event)
    {
        $request = $event->getRequest();
        if ($request instanceof ConsoleRequest) {
            return true;
        }

        $auth = new AuthenticationService();
        //ALREADY LOGGED IN
        //	user has auth,
        if ($auth->hasIdentity()) {
            return true;
        //NOT LOGGED IN
        //
        } else {
            /** @var $request \Zend\Http\PhpEnvironment\Request */
            $cookies = $request->getCookie();
            /** @var $cookies \Zend\Http\Header\Cookie */
            $userService = $this->getServiceLocator()->get('Stjornvisi\Service\User');
            /** @var $user \Stjornvisi\Service\User */
            if ($cookies && $cookies->offsetExists('backpfeifengesicht')) {
                if (($user = $userService->getByHash($cookies->offsetGet('backpfeifengesicht')))!=false) {
                    $authAdapter = $this->getServiceLocator()->get('Stjornvisi\Auth\Adapter');
                    $authAdapter->setIdentifier($user->id);
                    $result = $auth->authenticate($authAdapter);
                    $result->isValid();
                }
            }
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

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}
