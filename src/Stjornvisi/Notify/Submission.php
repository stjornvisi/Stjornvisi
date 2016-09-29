<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\Group as GroupService;
use Stjornvisi\Service\User;

/**
 * Handler for when a user registers / un-registers to a group.
 *
 * @package Stjornvisi\Notify
 */
class Submission extends AbstractNotifier
{
    protected function getRequiredData()
    {
        return ['group_id', 'recipient', 'register'];
    }

    /**
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        $groupObject = $this->getGroup($this->params->group_id);
        $userObject = $this->getUser($this->params->recipient);

        $body = $this->createEmailBody(
            [
                'group-register' => 'group-register',
                'group-unregister' => 'group-unregister',
            ],
            [

                'user' => $userObject,
                'group' => $groupObject
            ],
            ($this->params->register) ? 'group-register' : 'group-unregister'
        );

        $result = new Mail();
        $result->name = $userObject->name;
        $result->email = $userObject->email;
        $result->subject = ($this->params->register)
            ? "Þú hefur skráð þig í hópinn: {$groupObject->name}"
            : "Þú hefur afskráð þig úr hópnum: {$groupObject->name}";
        $result->body = $body;
        $result->test = true;

        $this->sendEmail($result);
        return $this;
    }

    /**
     * @param $id
     * @return bool|\stdClass
     * @throws \Stjornvisi\Service\Exception
     * @throws \Stjornvisi\Notify\NotifyException
     */
    private function getUser($id)
    {
        $userService = $this->getServiceLocator()->get(User::class);

        if (($user = $userService->get($id)) != false) {
            return $user;
        } else {
            throw new NotifyException("User [{$id}] not found");
        }
    }

    /**
     * @param $id
     * @return bool|\stdClass
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getGroup($id)
    {
        $groupService = $this->getServiceLocator()->get(GroupService::class);

        if (($group = $groupService->get($id)) != false) {
            return $group;
        } else {
            throw new NotifyException("Group [{$id}] not found");
        }
    }
}
