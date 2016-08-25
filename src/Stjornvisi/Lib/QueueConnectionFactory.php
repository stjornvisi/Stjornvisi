<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 09/10/14
 * Time: 22:37
 */

namespace Stjornvisi\Lib;

use PhpAmqpLib\Connection\AMQPConnection;
use Stjornvisi\Module;

class QueueConnectionFactory implements QueueConnectionFactoryInterface
{
    private $config = array();

    const MAIL_QUEUE = 'mail_queue';

    const NOTIFY_QUEUE = 'notify_queue';

    public function __construct($host = 'localhost', $port = 5672, $user = 'guest', $password = 'guest')
    {
        $this->config = array(
            'host' => $host,
            'port' => $port,
            'user' => $user,
            'password' => $password,
        );
    }

    public static function getMailQueueName()
    {
        return static::getQueueName(self::MAIL_QUEUE);
    }

    public static function getNotifyQueueName()
    {
        return static::getQueueName(self::NOTIFY_QUEUE);
    }

    public static function getQueueName($name)
    {
        $appEnv = Module::getApplicationEnv();
        $postfix = ($appEnv == Module::ENV_PRODUCTION) ? '' : "_$appEnv";
        return $name . $postfix;
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
