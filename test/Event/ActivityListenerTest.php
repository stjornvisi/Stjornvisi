<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 19/04/15
 * Time: 11:38 AM
 */

namespace Stjornvisi\Event;

use Monolog\Logger;
use \PHPUnit_Framework_TestCase;
use Stjornvisi\Service\Event;
use Stjornvisi\Service\News;
use Stjornvisi\Service\Company;
use Stjornvisi\Service\User;

class ActivityListenerTest extends PHPUnit_Framework_TestCase
{

    public function dataProvider()
    {
        return [
            [new \Zend\EventManager\Event(
                'create',
                new Event(),
                [
                    0=>'create',
                    'data'=> [
                        'subject' => 's1',
                        'id' => 1
                    ]
                ]
            )],

            [new \Zend\EventManager\Event(
                'update',
                new Event(),
                [
                    0=>'update',
                    'data'=> [
                        'subject' => 's1',
                        'id' => 1
                    ]
                ]
            )],

            [new \Zend\EventManager\Event(
                'delete',
                new Event(),
                [
                    0=>'delete',
                    'data'=> [
                        'subject' => 's1',
                        'id' => 1
                    ]
                ]
            )],

            [new \Zend\EventManager\Event(
                'create',
                new News(),
                [
                    0=>'create',
                    'data'=> [
                        'title' => 's1',
                        'id' => 1
                    ]
                ]
            )],

            [new \Zend\EventManager\Event(
                'update',
                new News(),
                [
                    0=>'update',
                    'data'=> [
                        'title' => 's1',
                        'id' => 1
                    ]
                ]
            )],

            [new \Zend\EventManager\Event(
                'delete',
                new News(),
                [
                    0=>'delete',
                    'data'=> [
                        'title' => 's1',
                        'id' => 1
                    ]
                ]
            )],

            [new \Zend\EventManager\Event(
                'create',
                new Company(),
                [
                    0=>'create',
                    'data'=> [
                        'name' => 's1',
                        'id' => 1
                    ]
                ]
            )],

            [new \Zend\EventManager\Event(
                'update',
                new Company(),
                [
                    0=>'update',
                    'data'=> [
                        'name' => 's1',
                        'id' => 1
                    ]
                ]
            )],

            [new \Zend\EventManager\Event(
                'delete',
                new Company(),
                [
                    0=>'delete',
                    'data'=> [
                        'name' => 's1',
                        'id' => 1
                    ]
                ]
            )],

            [new \Zend\EventManager\Event(
                'create',
                new User(),
                [
                    0=>'create',
                    'data'=> [
                        'name' => 's1',
                        'id' => 1
                    ]
                ]
            )],
        ];
    }

    /**
     * @param $event
     * @dataProvider dataProvider
     */
    public function testEvent($event)
    {
        $mock = \Mockery::mock('\Stjornvisi\Lib\QueueConnectionFactoryInterface')
            ->shouldReceive('createConnection')
            ->andReturn(
                \Mockery::mock('\PhpAmqpLib\Connection\AMQPConnection')
                    ->shouldReceive([
                        'channel'=>\Mockery::mock()
                            ->shouldReceive('queue_declare', 'close')
                            ->getMock()
                            ->shouldReceive('basic_publish')->twice()
                            ->getMock(),
                        'close' => ''
                    ])
                    ->getMock()
            )->getMock();


        $listener = new ActivityListener(new Logger(''));
        $listener->setQueueConnectionFactory($mock);
        $listener->log($event);
    }

}