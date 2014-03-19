<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/29/14
 * Time: 11:23 PM
 */

namespace Stjornvisi\Mail\Service;


interface ServiceInterface {
    /**
     * Set in which group this mail message
     * comes from.
     *
     * @param $id
     * @return ServiceInterface
     */
    public function setGroup( $id );
    /**
     * Set many email addresses at once.
     *
     * The key has to be an email address,
     * the value has to be a name.
     *
     * @param array $values
     * @return ServiceInterface
     */
    public function setTo(array $values);
    /**
     * Add an email/name address to recipients.
     *
     * @param string $email
     * @param string $name
     * @return ServiceInterface
     */
    public function addTo( $email, $name = null );

    /**
     * Set subject.
     *
     * @param string $subject
     * @return ServiceInterface
     */
    public function setSubject( $subject );

    /**
     * Set body.
     *
     * @param body $body
     * @return ServiceInterface
     */
    public function setBody( $body );

    /**
     * Send mail.
     * If $priority is set to (bool)true,
     * the mail is set to the top of the queue.
     *
     * @param bool $priority
     * @return ServiceInterface
     */
    public function send( $priority = false );
} 