<?php

namespace Stjornvisi\Notify;

use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactory;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;
use Stjornvisi\Notify\Message\Mail;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;

abstract class AbstractNotifier implements NotifyInterface,
                                           QueueConnectionAwareInterface,
                                           NotifyEventManagerAwareInterface,
                                           ServiceLocatorAwareInterface
{
    /**
     * @var \stdClass
     */
    protected $params;

    /**
     * @var  \Psr\Log\LoggerInterface;
     */
    protected $logger;

    /**
     * @var \Stjornvisi\Lib\QueueConnectionFactoryInterface
     */
    protected $queueFactory;

    /**
     * @var \Zend\EventManager\EventManager
     */
    protected $events;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Set a logger object to monitor the handler.
     *
     * @param LoggerInterface $logger
     *
     * @return $this|NotifyInterface
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Set Queue factory.
     *
     * @param QueueConnectionFactoryInterface $factory
     *
     * @return $this|NotifyInterface
     */
    public function setQueueConnectionFactory(
        QueueConnectionFactoryInterface $factory
    ) {
        $this->queueFactory = $factory;
        return $this;
    }

    /**
     * Get event manager
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Set EventManager
     *
     * @param EventManagerInterface $events
     *
     * @return $this|void
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers([
            __CLASS__,
            get_called_class(),
        ]);
        $this->events = $events;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return $this
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * @param \stdClass $data
     *
     * @return $this
     * @throws NotifyException
     */
    public function setData($data)
    {
        $this->params = $data->data;
        foreach ($this->getRequiredData() as $key) {
            if (!property_exists($this->params, $key)) {
                throw new NotifyException('Missing data:' . $key);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    abstract protected function getRequiredData();

    /**
     * Generate an unique hash for this
     * action.
     *
     * @return string
     */
    protected function getHash()
    {
        return md5(time() + rand(0, 1000));
    }

    protected function createEmailBody(
        $scripts,
        $childData,
        $template = 'script'
    ) {
        $renderer = $this->createEmailRenderer($scripts, $childData, $template);
        $this->renderChildren($renderer);
        return $renderer->render($renderer->get('layout'));
    }

    protected function createEmailRenderer(
        $scripts,
        $childData,
        $template = 'script'
    ) {
        //VIEW
        //	create and configure view
        $child = new ViewModel($childData);
        $child->setTemplate($template);

        $layout = new ViewModel();
        $layout->setTemplate('layout');
        $layout->addChild($child, 'content');

        $phpRenderer = new PhpRenderer();
        $phpRenderer->setCanRenderTrees(true);

        $viewFolder = __DIR__ . '/../../../view/';
        $map = [
            'layout' => $viewFolder . 'layout/email.phtml'
        ];
        if (!is_array($scripts)) {
            $scripts = ['script' => $scripts];
        }
        foreach ($scripts as $key => $script) {
            $map[$key] = $viewFolder . 'email/' . $script . '.phtml';
        }

        $resolver = new TemplateMapResolver();
        $resolver->setMap($map);
        $phpRenderer->setResolver($resolver);

        $phpRenderer->setVars([
            'layout' => $layout,
            'child'  => $child,
        ]);

        return $phpRenderer;
    }

    protected function renderChildren(
        PhpRenderer $phpRenderer,
        ViewModel $layout = null
    ) {
        if (!$layout) {
            $layout = $phpRenderer->get('layout');
        }
        foreach ($layout as $child) {
            $child->setOption('has_parent', true);
            $result = $phpRenderer->render($child);
            $child->setOption('has_parent', null);
            $capture = $child->captureTo();
            if (!empty($capture)) {
                $layout->setVariable($capture, $result);
            }
        }
    }

    protected function renderBody(PhpRenderer $renderer)
    {
        return $renderer->render($renderer->get('layout'));
    }

    /**
     * @param Mail $mail
     *
     * @throws NotifyException
     */
    protected function sendEmail(Mail $mail)
    {
        $this->sendEmails([$mail], function ($r) {
            return $r;
        });
    }

    /**
     * @param array    $data
     * @param callable $callback
     *
     * @throws NotifyException
     */
    protected function sendEmails($data, $callback)
    {
        try {
            $connection = $this->queueFactory->createConnection();
            $channel = $connection->channel();
            $queue = QueueConnectionFactory::getMailQueueName();
            $channel->queue_declare($queue, false, true, false, false);

            $numMessages = count($data);
            if ($numMessages > 1) {
                $this->logger->debug("Sending $numMessages");
            }

            foreach ($data as $item) {
                /** @var Mail $result */
                $result = $callback($item);

                $msg = new AMQPMessage(
                    $result->serialize(), ['delivery_mode' => 2]
                );
                $email = $result->email;

                $this->logger->info("Email sent to [{$email}]");

                $channel->basic_publish($msg, '', $queue);
            }

            if ($numMessages > 1) {
                $this->logger->debug("All messages sent");
            }

        } catch (\Exception $e) {
            throw new NotifyException($e->getMessage(), 0, $e);
        } finally {
            if (isset($channel) && $channel) {
                $channel->close();
            }
            if (isset($connection) && $connection) {
                $connection->close();
            }

            $user = null;
        }
    }
}
