<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 16/04/15
 * Time: 7:19 PM
 */

namespace Stjornvisi\Notify;

use \DateTime;
use \DateInterval;
use Stjornvisi\Module;
use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\Event as EventService;
use Stjornvisi\Service\Exception as ServiceException;
use Stjornvisi\Service\News as NewsService;
use Stjornvisi\Service\User as UserService;
use Zend\Authentication\AuthenticationService;

class Digest extends AbstractNotifier
{

    /**
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        //ID
        //  create an ID for this digest
        $emailId = $this->getHash();

        //TIME RANGE
        //	calculate time range and create from and
        //	to date objects for the range.
        $from = new DateTime();
        $from->add(new DateInterval('P1D'));
        $to = new DateTime();
        $to->add(new DateInterval('P8D'));

        $this->logger->info("Queue Service says: Fetching upcoming events");

        //EVENTS
        //  fetch all events
        $events = $this->getEvents($from, $to);

        //NO EVENTS
        //	if there are no events to publish, then it's no need
        //	to keep on processing this script
        if (count($events) == 0) {
            $this->logger->info("Digest, no events registered, stop");
            return $this;
        } else {
            $this->logger->info("Digest, ".count($events)." events registered.");
        }

        //USERS
        //	get all users who want to know
        //	about the upcoming events.
        $users = $this->getUsers();
        $this->logger->info("Digest, ".count($users)." user will get email ");


        $renderer = $this->createEmailRenderer('news-digest', [
            'events' => $events,
            'news' => $this->getNews(),
            'from' => $from,
            'to' => $to,
        ]);

        $this->sendEmails($users, function($user) use ($emailId, $renderer, $from, $to) {
            $renderer->get('child')->setVariable('user', $user);
            $this->renderChildren($renderer);
            $result = new Mail();
            $result->name = $user->name;
            $result->email = $user->email;
            $result->subject = "Vikan framundan | {$from->format('j. n.')} - {$to->format('j. n. Y')}";
            $result->body = $this->renderBody($renderer);
            $result->id = $emailId;
            $result->user_id = md5((string)$emailId . $user->email);
            $result->type = 'Digest';
            $result->parameters = 'allir';
            $result->test = false;
            return $result;
        });
        return $this;
    }

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return array
     * @throws ServiceException
     */
    private function getEvents(DateTime $from, DateTime $to)
    {
        $eventService = $this->getServiceLocator()->get(EventService::class);

        return $eventService->getRange($from, $to);
    }

	/**
     * @return bool|mixed
     * @throws ServiceException
     */
    private function getNews()
    {
        $newsService = $this->getServiceLocator()->get(NewsService::class);
        return $newsService->getNext();
    }

    /**
     * @return array
     * @throws ServiceException
     */
    private function getUsers()
    {
        $userService = $this->getServiceLocator()->get(UserService::class);
        if (Module::isStaging()) {
            $authService = $this->getServiceLocator()->get(AuthenticationService::class);
            if (!$authService->hasIdentity()) {
                throw new ServiceException("Can not send digest mail for Staging without an user");
            }
            return [$userService->get($authService->getIdentity()->id)];
        }
        return $userService->fetchAllForEmail('email_event_upcoming');
    }

    /**
     * @return array
     */
    protected function getRequiredData()
    {
        return [];
    }
}
