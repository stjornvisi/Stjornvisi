<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\Event;
use Stjornvisi\Service\User;

/**
 * Handler to send attendance message to users after they
 * have registered to event.
 *
 * @package Stjornvisi\Notify
 */
class Attend extends AbstractNotifier
{
    /**
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        //DATA-OBJECTS
        //  get data-objects from persistence layer.
        $eventObject = $this->getEvent($this->params->event_id);
        $userObject = $this->getUser($this->params->recipients);

        $body = $this->createEmailBody(
            [
                'attend'   => 'attending',
                'unattend' => 'un-attending',
            ],
            [
                'user'  => $userObject,
                'event' => $eventObject
            ],
            $this->params->type ? 'attend' : 'unattend'
        );
        $subject = ($this->params->type)
            ? "Þú hefur skráð þig á viðburðinn: {$eventObject->subject}"
            : "Þú hefur afskráð þig af viðburðinum: {$eventObject->subject}";
        $this->logger->info(
            "{$userObject->email} is ".
            ($this->params->type?'':'not ').
            "attending {$eventObject->subject}"
        );
        $this->sendEmail(new Mail([
            'name' => $userObject->name,
            'email' => $userObject->email,
            'subject' => $subject,
            'body' => $body
        ]));

        return $this;
    }

    /**
     * Get the recipient.
     *
     * @param $recipient
     * @return bool|null|object|\stdClass
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getUser($recipient)
    {
        if (!$recipient) {
            throw new NotifyException('No recipient provided');
        }

        $user = $this->getServiceLocator()->get(User::class);

        $userObject = null;

        //USER
        //	user can be in the system or he can be
        //	a guest, we have to prepare for both.
        if (is_numeric($recipient)) {
            $userObject = $user->get($recipient);
            if (!$userObject) {
                throw new NotifyException("User [{$recipient}] not found");
            }
        } else {
            $userObject = (object)array(
                'name' => $recipient->name,
                'email' => $recipient->email
            );
        }

        return $userObject;
    }

    /**
     * @param $event_id
     * @return bool|\stdClass|Event
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getEvent($event_id)
    {
        $event = $this->getServiceLocator()->get(Event::class);
        if (($event = $event->get($event_id)) != false) {
            return $event;
        } else {
            throw new NotifyException("Event [{$event_id}] not found");
        }

    }

    /**
     * @return array
     */
    protected function getRequiredData()
    {
        return ['event_id', 'recipients', 'type'];
    }
}
