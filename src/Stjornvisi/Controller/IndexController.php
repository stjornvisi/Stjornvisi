<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Stjornvisi\Controller;

use Stjornvisi\Lib\Time;
use Stjornvisi\Service\Event;
use Stjornvisi\Service\News;
use Stjornvisi\Service\Group;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Element\DateTime;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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
        //  load all servicesE
        $sm = $this->getServiceLocator();
        /** @var News $newsService */
        $newsService = $sm->get('Stjornvisi\Service\News');
        /** @var Event $eventService */
        $eventService = $sm->get('Stjornvisi\Service\Event');
        /** @var Group $groupService */
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $companyService = $sm->get('Stjornvisi\Service\Company');

        //AUTH
        //	authenticate user
        $auth = new AuthenticationService();
        if ($auth->hasIdentity()) {
            return new ViewModel([
                'groups' => $groupService->fetchDetails($auth->getIdentity()->id),
                'newsPrimary' => $newsService->getNotGrouped(1),
                'eventNews' => $newsService->getEventNews(3),
                'events' => $eventService->getByUser($auth->getIdentity()->id, 3),
                'eventCount' => $eventService->fetchUpcomingCount(),
                'eventsAttending' => $eventService->getAttendingByUser($auth->getIdentity()->id),
                'is_connected' => $companyService->getByUser($auth->getIdentity()->id),
                'identity' => $auth->getIdentity()
            ]);
        } else {
            return new ViewModel([
                'groups' => $groupService->fetchDetails(),
                'newsPrimary' => $newsService->getNotGrouped(1),
                'eventNews' => array(),
                'news' => $newsService->fetchAll(null),
                'identity' => null,
                'events' => $eventService->fetchUpcoming(),
                'eventCount' => $eventService->fetchUpcomingCount(),
                'eventsPassed' => $eventService->fetchPassed(),
                // 'gallery' => $eventService->fetchGallery(12, true),
            ]);
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
