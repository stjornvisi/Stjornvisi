<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/01/15
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\User as UserService;

/**
 * Facebbok OAuth URL sent to user in an e-mail.
 *
 * @package Stjornvisi\Notify
 */
class UserValidate extends AbstractNotifier
{
    protected function getRequiredData()
    {
        return ['facebook', 'user_id'];
    }

    /**
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        //USER
        //	get the user.
        $user = $this->getUser($this->params->user_id);

        $this->logger->debug("User validate [{$user->email}]");

        $body = $this->createEmailBody('user-validate', [
            'user' => $user,
            'link' => $this->params->facebook
        ]);

        $result = new Mail();
        $result->name = $user->name;
        $result->email = $user->email;
        $result->subject = "Stjórnvísi, staðfesting á aðgangi";
        $result->body = $body;
        $result->test = true;

        $this->sendEmail($result);

        return $this;
    }

    /**
     * @param int $id
     * @return bool|\stdClass
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    public function getUser($id)
    {
        $userService = $this->getServiceLocator()->get(UserService::class);

        if (($user = $userService->get($id)) != false) {
            return $user;
        } else {
            throw new NotifyException("User [{$id}] not found");
        }

    }
}
