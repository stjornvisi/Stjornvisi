<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Module;
use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\Group as GroupService;
use Stjornvisi\Service\User as UserService;
use Stjornvisi\View\Helper\Paragrapher;

/**
 * Email sent from a group to all or board.
 *
 * @package Stjornvisi\Notify
 */
class Group extends AbstractNotifier
{
    protected function getRequiredData()
    {
        return ['recipients', 'test', 'sender_id', 'group_id', 'body', 'subject'];
    }

    /**
     * Run the handler.
     *
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        $emailId = $this->getHash();

        $users = $this->getUsers(
            $this->params->recipients,
            $this->params->test,
            $this->params->sender_id,
            $this->params->group_id
        );
        $group = $this->getGroup($this->params->group_id);

        $this->logger->info("Group-email in " . ( $this->params->test?'':'none' ) . " test mode");

        $renderer = $this->createEmailRenderer('group-letter', [
            'user' => null,
            'group' => $group,
            'body' => call_user_func(new Paragrapher(), $this->params->body)
        ]);

        $func = function($user) use ($emailId, $renderer, $group) {
            $renderer->get('child')->setVariable('user', $user);
            $this->renderChildren($renderer);

            $result = new Mail();
            $result->name = $user->name;
            $result->email = $user->email;
            $result->subject = $this->params->subject;
            $result->body = $this->renderBody($renderer);
            $result->user_id = md5((string)$emailId . $user->email);
            $result->id = $emailId;
            $result->type = 'Event';
            $result->entity_id = $group->id;
            $result->parameters = $this->params->recipients;
            $result->test = $this->params->test;

            return $result;
        };

        $this->sendEmails($users, $func);

        return $this;
    }

    /**
     * @param string $recipients
     * @param bool $test
     * @param int $sender_id
     * @param int $group_id
     * @return \stdClass[]
     * @throws \Stjornvisi\Service\Exception
     */
    private function getUsers($recipients, $test, $sender_id, $group_id)
    {
        $userService = $this->getServiceLocator()->get(UserService::class);

        //ALL OR FORMEN
        //	send to all members of group or forman
        $types = ($recipients == 'allir')
            ? null  //everyone
            : [1, 2] ; // all managers

        //TEST OR REAL
        //	if test, send ony to sender, else to all
        return ($test || Module::isStaging())
            ? [$userService->get($sender_id)]
            : $userService->fetchUserEmailsByGroup([$group_id], $types);
    }

    /**
     * @param $group_id
     * @return bool|\stdClass
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getGroup($group_id)
    {
        $groupService = $this->getServiceLocator()->get(GroupService::class);

        if (($group = $groupService->get($group_id))!= false) {
            return $group;
        } else {
            throw new NotifyException("Group [{$group_id}] not found");
        }
    }
}
