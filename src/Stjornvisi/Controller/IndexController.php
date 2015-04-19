<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Stjornvisi\Controller;

use PhpAmqpLib\Message\AMQPMessage;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\FeedModel;
use Zend\Feed\Writer\Feed;
use Zend\Authentication\AuthenticationService;
use \DateTime;
use \DateInterval;

use Stjornvisi\Form\Login;

/**
 * Class IndexController.
 *
 * @package Stjornvisi\Controller
 */
class IndexController extends AbstractActionController
{
    /**
     * This is the landing page or the home page.
     *
     * It will display a <em>welcome</em> and a <em>sales pitch</em>
     * if the use is not logged in, else it will be the user's personal
     * profile.
     */
    public function indexAction()
    {
        //SERVICES
        //  load all services
        $sm = $this->getServiceLocator();
        $newsService = $sm->get('Stjornvisi\Service\News');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $companyService = $sm->get('Stjornvisi\Service\Company');

        //AUTH
        //	authenticate user
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            return new ViewModel(
                [
                'groups' => $groupService->getByUser($auth->getIdentity()->id),
                'news' => $newsService->getByUser($auth->getIdentity()->id),
                'events' => $eventService->getByUser($auth->getIdentity()->id),
                'gallery' => $eventService->fetchGallery(16),
                'media' => $eventService->getMediaByUser($auth->getIdentity()->id),
                'is_connected' => $companyService->getByUser($auth->getIdentity()->id),
                'identity' => $auth->getIdentity()
                ]
            );
        } else {
            return new ViewModel(
                [
                'identity' => null,
                'groups' => $groupService->fetchAll(),
                'event' => $eventService->getNext(),
                'news' => $newsService->getNext(),
                'gallery' => $eventService->fetchGallery(12, true),
                ]
            );
        }

    }

    /**
     * @todo do we need this?
     */
    public function radstefnaAction()
    {
    }

    /**
     * @todo do we need this?
     */
    public function semposiumThanksAction()
    {
    }

    /**
     * @todo do we need this?
     */
    public function semposiumAttendanceAction()
    {
    }

    /**
     * @todo do we need this?
     * @return ViewModel
     */
    public function stjornvisiOverviewAction()
    {
        return new ViewModel(['identity' => null]);
    }

    /**
     * This just return an (almost) static page
     * with the style-guide
     */
    public function styleGuideAction()
    {
    }
}
