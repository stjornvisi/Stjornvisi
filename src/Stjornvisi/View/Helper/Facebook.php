<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/10/14
 * Time: 4:47 PM
 */

namespace Stjornvisi\View\Helper;

use Stjornvisi\Module;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookSession;

class Facebook extends AbstractHelper implements ServiceLocatorAwareInterface
{
    /** @var ServiceLocatorInterface */
    private $serviceLocator;

    /**
     *
     * @param string $value
     * @return string
     */
    public function __invoke($value = 'your/redirect/URL/here')
    {
        $config = $this->getServiceLocator()->getServiceLocator()->get('Config');
        FacebookSession::setDefaultApplication(
            $config['facebook']['appId'],
            $config['facebook']['secret']
        ); //TODO should this be in a global space
        $server = Module::getServerUrl();
        $helper = new FacebookRedirectLoginHelper($server.$value);
        return $helper->getLoginUrl();
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Facebook
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
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
