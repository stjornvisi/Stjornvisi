<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/01/15
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Lib\QueueConnectionFactory;
use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\User;
use Psr\Log\LoggerInterface;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use Zend\EventManager\EventManager;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\EventManager\EventManagerInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Facebbok OAuth URL sent to user in an e-mail.
 *
 * @package Stjornvisi\Notify
 */
class UserValidate implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface
{
    /**
     * @var  \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \stdClass
     */
    private $params;

    /**
     * @var \Stjornvisi\Lib\QueueConnectionFactoryInterface
     */
    private $queueFactory;

    /**
     * @var array
     */
    private $dataStore;

    /**
     * @var \Zend\EventManager\EventManager
     */
    protected $events;

    /**
     * Set logger to monitor.
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
     * @param \stdClass $data
     * @return $this
     * @throws NotifyException
     */
    public function setData($data)
    {
        if (property_exists($data, 'facebook')) {
            throw new NotifyException('Missing data:facebook');
        }

        if (property_exists($data, 'user_id')) {
            throw new NotifyException('Missing data:user_id');
        }
        $this->params = $data->data;
        return $this;
    }

    /**
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        //USER
        //	get the user.
        $user = $this->getUser($this->params->user_id);

        $this->logger->debug("User validate [{$user->email}]");

        //VIEW
        //	create and configure view
        $child = new ViewModel(array(
            'user' => $user,
            'link' => $this->params->facebook
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
            'script' => __DIR__ . '/../../../view/email/user-validate.phtml',
        ));
        $phpRenderer->setResolver($resolver);

        //MAIL
        //	now we want to send this to the user/quest via e-mail
        //	so we try to connect to Queue and send a message
        //	to mail_queue
        try {
            $connection = $this->queueFactory->createConnection();
            $channel = $connection->channel();
            $queue = QueueConnectionFactory::getMailQueueName();
            $channel->queue_declare($queue, false, true, false, false);

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
            $result->name = $user->name;
            $result->email = $user->email;
            $result->subject = "Stjórnvísi, staðfesting á aðgangi";
            $result->body = $phpRenderer->render($layout);
            $result->test = true;

            $msg = new AMQPMessage($result->serialize(), ['delivery_mode' => 2]);
            $this->logger->info("User validate email to [{$user->email}]");

            $channel->basic_publish($msg, '', $queue);

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

        return $this;
    }

    /**
     * Set Queue factory
     * @param QueueConnectionFactoryInterface $factory
     * @return $this|NotifyInterface
     */
    public function setQueueConnectionFactory(QueueConnectionFactoryInterface $factory)
    {
        $this->queueFactory = $factory;
        return $this;
    }

    /**
     * @param $config
     * @return $this
     */
    public function setDateStore($config)
    {
        $this->dataStore = $config;
        return $this;
    }

    /**
     * Set EventManager
     *
     * @param EventManagerInterface $events
     * @return $this|void
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;
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
     * @param int $id
     * @return bool|\stdClass
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    public function getUser($id)
    {
        $pdo = new \PDO(
            $this->dataStore['dns'],
            $this->dataStore['user'],
            $this->dataStore['password'],
            $this->dataStore['options']
        );
        $userService = new User();
        $userService->setDataSource($pdo)
            ->setEventManager($this->getEventManager());

        if (($user = $userService->get($id)) != false) {
            return $user;
        } else {
            throw new NotifyException("User [{$id}] not found");
        }

    }
}
