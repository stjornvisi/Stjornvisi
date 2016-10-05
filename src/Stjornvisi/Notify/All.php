<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Module;
use Stjornvisi\Service\User as UserService;
use Stjornvisi\View\Helper\Paragrapher;
use Stjornvisi\Notify\Message\Mail as MailMessage;

/**
 * Handler to notify everyone in the system. There is no way for a user
 * not to get message from this handler.
 *
 * Currently this handler only sends out e-mail messages.
 *
 * This will transcend all Group config. Only
 * Admin can do this.
 *
 * @package Stjornvisi\Notify
 */
class All extends AbstractNotifier
{
    /**
     * @return array
     */
    protected function getRequiredData()
    {
        return ['subject', 'recipients', 'sender_id', 'test', 'body'];
    }

    /**
     * Run the handler.
     *
     * @return $this|NotifyInterface
     * @throws NotifyException
     */
    public function send()
    {
        $emailId = $this->getHash();
        $users = $this->getUsers($this->params->sender_id, $this->params->recipients, $this->params->test);
        $this->logger->info("Notify All ({$this->params->recipients})");

        $renderer = $this->createEmailRenderer('letter', [
            'user' => null,
            'body' => call_user_func(new Paragrapher(), $this->params->body),
        ]);

        $func = function($user) use ($emailId, $renderer) {
            $renderer->get('child')->setVariable('user', $user);
            $this->renderChildren($renderer);

            $result = new MailMessage();
            $result->name = $user->name;
            $result->email = $user->email;
            $result->subject = $this->params->subject;
            $result->body = $this->renderBody($renderer);
            $result->id = $emailId;
            $result->user_id = md5((string)$emailId . $user->email);
            $result->entity_id = null;
            $result->type = 'All';
            $result->parameters = $this->params->recipients;
            $result->test = $this->params->test;

            return $result;
        };

        $this->sendEmails($users, $func);

        return $this;
    }

    /**
     * Find out who is actually getting this
     * e-mail.
     *
     * @param int $sender
     * @param string $recipients
     * @param bool $test
     * @return array
     * @throws \Stjornvisi\Service\Exception
     */
    private function getUsers($sender, $recipients, $test)
    {
        $user = $this->getServiceLocator()->get(UserService::class);

        if ($test || Module::isStaging()) {
            return [$user->get($sender)];
        } else {
           switch ($recipients){
               case "formenn" :
                   $recipientAddresses = $user->fetchAllChairmenForEmail();
                   break;
               case "stjornendur" :
                   $recipientAddresses = $user->fetchAllManagersForEmail();
                   break;
               case "allir" :
                   $recipientAddresses = $user->fetchAllForEmail();
                   break;
               default :
                   $recipientAddresses = [];
           }
        }
        return $recipientAddresses;
    }
}
