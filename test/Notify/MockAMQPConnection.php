<?php

namespace Stjornvisi\Notify;

use PhpAmqpLib\Connection\AMQPConnection;

require_once 'MockAMQPChannel.php';

class MockAMQPConnection extends AMQPConnection
{
    /** @var MockAMQPChannel */
    private $channel;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {

    }

    public function channel($channel_id = null)
    {
        $channel = new MockAMQPChannel($this);
        $this->channel = $channel;
        return $channel;
    }

    public function close($reply_code = 0, $reply_text = "", $method_sig = array(0, 0))
    {
        return '';
    }

    public function getChannel()
    {
        return $this->channel;
    }


}
