<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Stjornvisi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\FeedModel;
use Zend\Feed\Writer\Feed;
use Zend\Authentication\AuthenticationService;
use \DateTime;
use \DateInterval;

use Stjornvisi\Form\Login;


class IndexController extends AbstractActionController{


    /**
     * This is the landing page or the home page.
     *
     * It will display a <em>welcome</em> and a <em>sales pitch</em>
     * if the use is not logged in, else it will be the user's personal
     * profile.
     *
     * @todo create splash/landing for anonymous users
     */
    public function indexAction(){

        //SERVICES
        //  load all services
        $sm = $this->getServiceLocator();
        $newsService = $sm->get('Stjornvisi\Service\News');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $companyService = $sm->get('Stjornvisi\Service\Company');


        $auth = new AuthenticationService();


        if( $auth->hasIdentity() ){
            return new ViewModel(array(
                'groups' => $groupService->getByUser( $auth->getIdentity()->id ),
                'news' => $newsService->getByUser( $auth->getIdentity()->id ),
                'events' => $eventService->getByUser( $auth->getIdentity()->id ),
                'media' => $eventService->getMediaByUser( $auth->getIdentity()->id ),
                'is_connected' => $companyService->getByUser( $auth->getIdentity()->id ),
                'identity' => $auth->getIdentity()
            ));
        }else{
            return new ViewModel(array(
                'identity' => null,
				'groups' => $groupService->fetchAll(),
				'event' => $eventService->getNext(),
				'news' => $newsService->getNext(),
                'gallery' => $eventService->fetchGallery(10)
            ));
        }

    }


    public function sitemapRobotAction(){
        $this->getHelper('layout')->disableLayout();
    }

    /**
     * RSS feed for all events
     *
     * Get all events from two months back in time
     * @todo change content-type to application/rss+xml
     *      disable layout
     */ /*
    public function rssEventsAction(){

        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $from = new DateTime();
        $from->sub( new DateInterval('P2M') );

        $feed = new Feed();
        $feed->setTitle('Feed Example');
        $feed->setFeedLink('http://ourdomain.com/rss', 'atom');
        $feed->addAuthor(array(
            'name'  => 'Stjórnvísi',
            'email' => 'stjornvisi@stjornvisi.is',
            'uri'   => 'http://stjornvisi.is',
        ));
        $feed->setDescription('Viðburðir');
        $feed->setLink('http://ourdomain.com');
        $feed->setDateModified(new DateTime());

        $data = array();

        foreach($eventService->getRange( $from ) as $row){
            //create entry...
            $entry = $feed->createEntry();
            $entry->setTitle($row->subject);
            $entry->setLink( 'http://stjornvisi.is/' );
            $entry->setDescription($row->body.'.');

            $entry->setDateModified($row->event_date);
            $entry->setDateCreated($row->event_date);

            $feed->addEntry($entry);
        }

        $feed->export('rss');

        $feedmodel = new FeedModel();
        $feedmodel->setFeed($feed);

        return $feedmodel;

    }*/

    /**
     * RSS feed for all news
     *
     * Get all news from two months back in time
     * @todo change content-type to application/rss+xml
     *      disable layout
     */ /*
    public function rssNewsAction(){
        $sm = $this->getServiceLocator();
        $newsService = $sm->get('Stjornvisi\Service\News');
        $from = new DateTime();
        $from->sub( new DateInterval('P2M') );

        return new ViewModel(array(
            'news' => $newsService->getRange( $from )
        ));
    }*/

    /**
     * @todo do we need this?
     */
    public function radstefnaAction(){

    }

    /**
     * @todo do we need this?
     */
    public function semposiumThanksAction(){

    }

    /**
     * @todo do we need this?
     */
    public function semposiumAttendanceAction(){
        //$semposiumDAO = new Application_Model_Semposium();
        //$this->view->attendance = $semposiumDAO->fetchAll(null, "name");
    }

}
