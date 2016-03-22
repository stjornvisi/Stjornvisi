<?php

namespace Stjornvisi\Notify;

use PhpAmqpLib\Connection\AMQPConnection;

require_once 'MockAMQPChannel.php';

class MockAMQPConnection extends AMQPConnection
{
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {

    }

    public function channel($channel_id = null)
    {
        return new MockAMQPChannel($this);
    }

    public function close($reply_code = 0, $reply_text = "", $method_sig = array(0, 0))
    {
        return '';
    }


}
