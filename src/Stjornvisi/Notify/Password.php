<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Notify\Message\Mail;

/**
 * Get new password sent to user in e-mail
 *
 * @package Stjornvisi\Notify
 */
class Password extends AbstractNotifier
{
    /**
     * @return array
     */
    protected function getRequiredData()
    {
        return ['recipients', 'password'];
    }

    /**
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        $body = $this->createEmailBody('lost-password', [
            'user' => $this->params->recipients,
            'password' => $this->params->password,
        ]);

        $result = new Mail();
        $result->name = $this->params->recipients->name;
        $result->email = $this->params->recipients->email;
        $result->subject = "Nýtt lykilorð";
        $result->body = $body;
        $result->type = 'Password';
        $result->test = true;

        $this->sendEmail($result);

        return $this;
    }
}
