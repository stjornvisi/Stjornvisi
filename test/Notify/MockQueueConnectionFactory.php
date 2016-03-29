<?php

namespace Stjornvisi\Notify;

use Stjornvisi\Lib\QueueConnectionFactoryInterface;

require_once 'MockAMQPConnection.php';

class MockQueueConnectionFactory implements QueueConnectionFactoryInterface
{
    private $throwExceptionOnCreateConnection;
    /** @var MockAMQPConnection */
    private $connection;

    public function setConfig(array $config)
    {
    }

    /**
     * @return \PhpAmqpLib\Connection\AMQPConnection
     */
    public function createConnection()
    {
        if ($this->throwExceptionOnCreateConnection) {
            $className = $this->throwExceptionOnCreateConnection;
            $ex = new $className();
            throw $ex;
        }
        $connection = new MockAMQPConnection();
        $this->connection = $connection;
        return $connection;
    }

    public function setThrowExceptionOnCreateConnection($exceptionClassName = '\PhpAmqpLib\Exception\AMQPRuntimeException')
    {
        $this->throwExceptionOnCreateConnection = $exceptionClassName;
        return $this;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
