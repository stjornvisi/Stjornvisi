<?php

namespace Stjornvisi\Notify;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Stjornvisi\Bootstrap;
use Stjornvisi\DatabaseTestCase;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

require_once 'MockQueueConnectionFactory.php';

abstract class AbstractTestCase extends DatabaseTestCase
{
    /** @var MockQueueConnectionFactory */
    private $lastQueueConnectionFactory;

    protected function getQueueMock()
    {
        return new MockQueueConnectionFactory();
    }

    /**
     * @return Logger
     */
    protected function getNullLogger()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new NullHandler());
        return $logger;
    }

    /**
     * @param NotifyInterface|QueueConnectionAwareInterface|NotifyEventManagerAwareInterface $notifier
     * @param bool $throwOnCreateConnection
     */
    protected function prepareNotifier($notifier, $throwOnCreateConnection = false)
    {
        if ($notifier instanceof ServiceLocatorAwareInterface) {
            $notifier->setServiceLocator(Bootstrap::getServiceManager());
        }
        $notifier->setLogger($this->getNullLogger());
        $mock = $this->getQueueMock();
        if ($throwOnCreateConnection) {
            $mock->setThrowExceptionOnCreateConnection();
        }
        $notifier->setQueueConnectionFactory($mock);
        $this->lastQueueConnectionFactory = $mock;
    }

    /**
     * @param int $expectedCount
     */
    protected function checkNumChannelPublishes($expectedCount)
    {
        if ($channel = $this->getLastChannel()) {
            $actualCount = $channel->getTotalBasicPublish();
            $this->assertEquals($expectedCount, $actualCount);
        }
    }

    /**
     * @param string[] $expectedNames
     */
    protected function checkPublishedNames($expectedNames)
    {
        if ($channel = $this->getLastChannel()) {
            $actualNames = $channel->getNames();
            sort($actualNames);
            sort($expectedNames);
            $this->assertEquals($expectedNames, $actualNames);
        }
    }

    protected function checkChannelBody($contains, $num = 0, $shouldContain = true)
    {
        $bodies = $this->getLastChannel()->getBodies();
        $this->assertArrayHasKey($num, $bodies);
        if ($shouldContain) {
            $this->assertContains($contains, $bodies[$num]);
        }
        else {
            $this->assertNotContains($contains, $bodies[$num]);
        }
    }

    protected function checkChannelSubject($contains, $num = 0)
    {
        $subjects = $this->getLastChannel()->getSubjects();
        $this->assertArrayHasKey($num, $subjects);
        $this->assertContains($contains, $subjects[$num]);
    }

    protected function checkGreeting($name, $num = 0)
    {
        $this->checkChannelBody("l(l) $name</p>", $num);
    }


    /**
     * @param bool $throwOnCreateConnection
     * @param string $class
     *
     * @return AbstractNotifier
     */
    protected function createNotifier($throwOnCreateConnection = false, $class = null)
    {
        if (!$class) {
            $class = $this->getNotifierClass();
        }
        /** @var AbstractNotifier $notifier */
        $notifier = Bootstrap::getServiceManager()->get($class);
        $this->prepareNotifier($notifier, $throwOnCreateConnection);
        return $notifier;
    }

    /**
     * @return string
     */
    abstract protected function getNotifierClass();

    /**
     * @return MockAMQPChannel
     */
    protected function getLastChannel()
    {
        if (!$this->lastQueueConnectionFactory) {
            return null;
        }
        $connection = $this->lastQueueConnectionFactory->getConnection();
        if (!$connection) {
            return null;
        }
        return $connection->getChannel();
    }

}
