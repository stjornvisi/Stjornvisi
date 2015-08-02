<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Psr\Log\LoggerInterface;

use Stjornvisi\Notify\Message\Mail;
use Zend\EventManager\EventManager;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\EventManager\EventManagerInterface;

use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Get new password sent to user in e-mail
 *
 * @package Stjornvisi\Notify
 */
class Password implements NotifyInterface, QueueConnectionAwareInterface
{
    /**
     * @var \stdClass
     */
    private $params;

    /**
     * @var  \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Stjornvisi\Lib\QueueConnectionFactoryInterface
     */
    private $queueFactory;

    /**
     * @var \Zend\EventManager\EventManager
     */
    protected $events;

    /**
     * @param \stdClass $data
     * @return $this
     * @throws NotifyException
     */
    public function setData($data)
    {
        $this->params = $data->data;
        if (!property_exists($this->params, 'recipients')) {
            throw new NotifyException('Missing data:recipients');
        }
        if (!property_exists($this->params, 'password')) {
            throw new NotifyException('Missing data:passwords');
        }
        return $this;
    }

    /**
     * Set logger object to monitor this handler.
     *
     * @param LoggerInterface $logger
     * @return $this|NotifyInterface
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        //VIEW
        //	create and configure view
        $child = new ViewModel(array(
            'user' => $this->params->recipients,
            'password' => $this->params->password,
        ));
        $child->setTemplate('script');

        $layout = new ViewModel();
        $layout->setTemplate('layout');
        $layout->addChild($child, 'content');

        $phpRenderer = new PhpRenderer();
        $phpRenderer->setCanRenderTrees(true);

        $resolver = new Resolver\TemplateMapResolver();
        $resolver->setMap(array(
            'layout' => __DIR__ . '/../../../view/layout/email.phtml',
            'script' => __DIR__ . '/../../../view/email/lost-password.phtml',
        ));
        $phpRenderer->setResolver($resolver);
        foreach ($layout as $child) {
            $child->setOption('has_parent', true);
            $result  = $phpRenderer->render($child);
            $child->setOption('has_parent', null);
            $capture = $child->captureTo();
            if (!empty($capture)) {
                $layout->setVariable($capture, $result);
            }
        }

        $result = new Mail();
        $result->name = $this->params->recipients->name;
        $result->email = $this->params->recipients->email;
        $result->subject = "Nýtt lykilorð";
        $result->body = $phpRenderer->render($layout);
        $result->type = 'Password';
        $result->test = true;


        //MAIL
        //	now we want to send this to the user/quest via e-mail
        //	so we try to connect to Queue and send a message
        //	to mail_queue
        try {
            $connection = $this->queueFactory->createConnection();
            $channel = $connection->channel();
            $channel->queue_declare('mail_queue', false, true, false, false);

            $msg = new AMQPMessage($result->serialize(), ['delivery_mode' => 2]);
            $this->logger->info($this->params->recipients->name ." is requesting new password");

            $channel->basic_publish($msg, '', 'mail_queue');

        } catch (\Exception $e) {
            throw new NotifyException($e->getMessage(), 0, $e);
        } finally {
            if (isset($channel) && $channel) {
                $channel->close();
            }
            if (isset($connection) && $connection) {
                $connection->close();
            }
        }

        return $this;
    }

    /**
     * Set Queue factory
     *
     * @param QueueConnectionFactoryInterface $factory
     * @return $this|NotifyInterface
     */
    public function setQueueConnectionFactory(QueueConnectionFactoryInterface $factory)
    {
        $this->queueFactory = $factory;
        return $this;
    }
}
