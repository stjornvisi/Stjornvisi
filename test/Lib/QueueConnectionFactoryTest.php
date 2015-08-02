<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 02/02/15
 * Time: 10:38
 */

namespace Stjornvisi\Lib;

use PHPUnit_Framework_TestCase;

class QueueConnectionFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \PhpAmqpLib\Exception\AMQPRuntimeException
     */
    public function testFactoryException()
    {
        $factory = new QueueConnectionFactory();
        $factory->setConfig([
            'host' => '',
            'port' => '',
            'user' => '',
            'password' => ''
        ]);
        $factory->createConnection();
    }
}
