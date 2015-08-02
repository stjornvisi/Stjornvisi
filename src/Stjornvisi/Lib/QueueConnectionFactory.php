<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 09/10/14
 * Time: 22:37
 */

namespace Stjornvisi\Lib;

use PhpAmqpLib\Connection\AMQPConnection;

class QueueConnectionFactory implements QueueConnectionFactoryInterface
{
    private $config = array();

    public function __construct($host = 'localhost', $port = 5672, $user = 'guest', $password = 'guest')
    {
        $this->config = array(
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'password' => $password,
        );
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return AMQPConnection
     */
    public function createConnection()
    {
        return $connection = new AMQPConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password']
        );
    }
}
