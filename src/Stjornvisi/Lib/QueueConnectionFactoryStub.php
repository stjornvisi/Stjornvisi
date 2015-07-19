<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 10/07/15
 * Time: 10:39 AM
 */

namespace Stjornvisi\Lib;

class QueueConnectionFactoryStub implements QueueConnectionFactoryInterface
{

    public function setConfig(array $config)
    {

    }

    public function channel()
    {
        return $this;
    }

    public function queue_declare()
    {

    }

    public function basic_publish()
    {

    }

    public function close()
    {

    }

    /**
     * @return \PhpAmqpLib\Connection\AMQPConnection
     */
    public function createConnection()
    {
        return $this;
    }
}
