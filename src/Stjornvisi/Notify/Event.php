<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 28/09/14
 * Time: 22:42
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Module;
use Stjornvisi\Notify\Message\Mail as MailMessage;
use Stjornvisi\Service\Event as EventService;
use Stjornvisi\Service\User as UserService;
use Stjornvisi\View\Helper\Paragrapher;

/**
 * Emails sent from an event to all or attendees.
 *
 * @package Stjornvisi\Notify
 */
class Event extends AbstractNotifier
{
    /**
     * @return array
     */
    protected function getRequiredData()
    {
        return ['user_id', 'recipients', 'test', 'body', 'subject', 'event_id'];
    }

    /**
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        $emailId = $this->getHash();
        $event = $this->getEvent($this->params->event_id);
        $users = $this->getUser(
            $this->getGroupsFromEvent($event),
            $event->id,
            $this->params->user_id,
            $this->params->recipients,
            $this->params->test
        );
        $eventId = $event->id;

        $this->logger->info(
            (count($users)) . " user will get an email" .
            "in connection with event {$event->subject}:{$event->id}"
        );

        $renderer = $this->createEmailRenderer('event', [
            'user' => null,
            'event' => $event,
            'body' => call_user_func(new Paragrapher(), $this->params->body)
        ]);

        $func = function($user) use ($emailId, $renderer, $eventId) {
            $renderer->get('child')->setVariable('user', $user);
            $this->renderChildren($renderer);

            $message = new MailMessage();
            $message->name = $user->name;
            $message->email = $user->email;
            $message->subject = $this->params->subject;
            $message->body = $this->renderBody($renderer);
            $message->id = $emailId;
            $message->user_id = md5((string)$emailId . $user->email);
            $message->type = 'Event';
            $message->entity_id = $eventId;
            $message->parameters = $this->params->recipients;
            $message->test = $this->params->test;

            return $message;
        };

        $this->sendEmails($users, $func);
        return $this;
    }

    /**
     * @param array $groups
     * @param $eventId
     * @param $userId
     * @param $recipients
     * @param $test
     * @return \stdClass[]
     * @throws NotifyException
     */
    private function getUser(array $groups, $eventId, $userId, $recipients, $test)
    {
        $user = $this->getServiceLocator()->get(UserService::class);

        //TEST
        //	this is just a test message so we send it just to the user in question
        if ($test || Module::isStaging()) {
            if (($result = $user->get($userId)) != false) {
                return [$result];
            } else {
                throw new NotifyException("Sender not found");
            }

        //REAL
        //	this is the real thing.
        //	If $groupIds is NULL/empty, this this is a Stjornvisi Event and then
        //	we just fetch all valid users in the system.
        } else {
            $users = ($recipients == 'allir')
                ? (empty($groups))
                    ? $user->fetchAllForEmail()
                    : $user->fetchUserEmailsByGroup($groups, null, false, 'email_event_all')
                : $user->fetchUserEmailsByEvent($eventId) ;
            if (empty($users)) {
                throw new NotifyException("No users found for event notification");
            } else {
                return $users;
            }
        }
    }

    /**
     * @param int $eventId
     * @return bool|\stdClass
     * @throws NotifyException
     */
    private function getEvent($eventId)
    {
        $event = $this->getServiceLocator()->get(EventService::class);

        //EVENT
        //	first of all, find the event in question
        if (($event = $event->get($eventId)) != false) {
            return $event;
        } else {
            throw new NotifyException("Event [{$eventId}] not found");
        }
    }

    /**
     * Extract just the Group IDs from the event.
     *
     * @param $event
     * @return array
     */
    private function getGroupsFromEvent($event)
    {
        return array_map(
            function ($i) {
                return $i->id;
            },
            $event->groups
        );
    }
}
