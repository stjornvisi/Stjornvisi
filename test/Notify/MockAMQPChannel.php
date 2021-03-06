<?php

namespace Stjornvisi\Notify;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;

class MockAMQPChannel extends AMQPChannel
{
    private $totalBasicPublish = 0;
    private $names = [];
    private $subjects = [];
    private $bodies = [];

    /** @noinspection PhpMissingParentConstructorInspection
     * @param AbstractConnection $connection
     * @param null $channel_id
     * @param null $auto_decode
     */
    public function __construct(AbstractConnection $connection, $channel_id = null, $auto_decode = null)
    {
        $this->connection = $connection;
    }

    public function queue_declare($queue = "",
                                  $passive = false,
                                  $durable = false,
                                  $exclusive = false,
                                  $auto_delete = true,
                                  $nowait = false,
                                  $arguments = null,
                                  $ticket = null)
    {
        return '';
    }

    public function basic_publish($msg, $exchange = "", $routing_key = "",
                                  $mandatory = false, $immediate = false,
                                  $ticket = null)
    {
        $this->totalBasicPublish++;
        $json = json_decode($msg->body);
        if ($json) {
            if (isset($json->name)) {
                $this->names[] = $json->name;
            }
            if (isset($json->subject)) {
                $this->subjects[] = $json->subject;
            }
            if (isset($json->body)) {
                $this->bodies[] = $json->body;
            }
        }
    }

    public function close($reply_code = 0,
                          $reply_text = "",
                          $method_sig = array(0, 0))
    {
        return '';
    }

    /**
     * @return int
     */
    public function getTotalBasicPublish()
    {
        return $this->totalBasicPublish;
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * @return array
     */
    public function getSubjects()
    {
        return $this->subjects;
    }

    /**
     * @return array
     */
    public function getBodies()
    {
        return $this->bodies;
    }


}
