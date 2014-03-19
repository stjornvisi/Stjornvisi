<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/29/14
 * Time: 10:46 PM
 */

namespace Stjornvisi\Mail\Service;

use Zend\Log\LoggerInterface;

class File implements ServiceInterface {

    private $logger;

    private $group;
    private $to;
    private $subject;
    private $body;

    public function __construct( LoggerInterface $logger  ){
        $this->logger = $logger;
    }

    public function setGroup( $id ){
        $this->group = $id;
        return $this;
    }

    public function setTo(array $values){
        $this->to = $values;
        return $this;
    }
    public function addTo( $email, $name = null ){
        $this->to[$email] = $name;
        return $this;
    }
    public function setSubject( $subject ){
        $this->subject = $subject;
        return $this;
    }
    public function setBody( $body ){
        $this->body = $body;
        return $this;
    }
    public function send( $priority = false ){
        $emailName = implode(',',array_map(function($value,$key){
            return "[{$key}][{$value}] ";
        },$this->to,array_keys($this->to)));

        $this->logger->info(
            "\033[1;37m\033[44m{$emailName} : {$this->subject} | {$this->body}\033[0m"
        );
    }
} 