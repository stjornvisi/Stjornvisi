<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Lib\QueueConnectionFactory;
use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\Group as GroupService;
use Stjornvisi\Service\User;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use Zend\EventManager\EventManager;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Psr\Log\LoggerInterface;
use Zend\EventManager\EventManagerInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Handler for when a user registers / un-registers to a group.
 *
 * @package Stjornvisi\Notify
 */
class Submission implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface
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
     * @var array
     */
    private $dataStore;

    /**
     * @var \Zend\EventManager\EventManager
     */
    protected $events;

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @param \stdClass $data
     * @return $this
     * @throws NotifyException
     */
    public function setData($data)
    {
        $this->params = $data->data;
        if (!property_exists($this->params, 'group_id')) {
            throw new NotifyException('Missing data:group_id');
        }
        if (!property_exists($this->params, 'recipient')) {
            throw new NotifyException('Missing data:recipient');
        }
        if (!property_exists($this->params, 'register')) {
            throw new NotifyException('Missing data:register');
        }
        return $this;
    }

    /**
     * Set logger instance
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
        $groupObject = $this->getGroup($this->params->group_id);
        $userObject = $this->getUser($this->params->recipient);

        //VIEW
        //	create and configure view
        $child = new ViewModel(array(
            'user' => $userObject,
            'group' => $groupObject
        ));
        $child->setTemplate(($this->params->register)
            ? 'group-register'
            : 'group-unregister');

        $layout = new ViewModel();
        $layout->setTemplate('layout');
        $layout->addChild($child, 'content');

        $phpRenderer = new PhpRenderer();
        $phpRenderer->setCanRenderTrees(true);

        $resolver = new Resolver\TemplateMapResolver();
        $resolver->setMap(array(
            'layout' => __DIR__ . '/../../../view/layout/email.phtml',
            'group-register' => __DIR__ . '/../../../view/email/group-register.phtml',
            'group-unregister' => __DIR__ . '/../../../view/email/group-unregister.phtml',
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
        $result->name = $userObject->name;
        $result->email = $userObject->email;
        $result->subject = ($this->params->register)
            ? "Þú hefur skráð þig í hópinn: {$groupObject->name}"
            : "Þú hefur afskráð þig úr hópnum: {$groupObject->name}";
        $result->body = $phpRenderer->render($layout);
        $result->test = true;

        //MAIL
        //	now we want to send this to the user/quest via e-mail
        //	so we try to connect to Queue and send a message
        //	to mail_queue
        try {
            $connection = $this->queueFactory->createConnection();
            $channel = $connection->channel();
            $queue = QueueConnectionFactory::getMailQueueName();
            $channel->queue_declare($queue, false, true, false, false);
            $msg = new AMQPMessage($result->serialize(), ['delivery_mode' => 2]);

            $this->logger->info(get_class($this) .":send".
                " {$userObject->email} is " . ( ($this->params->register)?'':'not ' ) .
                "joining group {$groupObject->name_short}");

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
            $userObject     = null;
            $groupObject = null;
            $this->closeDataSourceDriver();
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
     * @param array $config
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
     * @param $id
     * @return \Stjornvisi\Service\User
     * @throws \Stjornvisi\Service\Exception
     * @throws \Stjornvisi\Notify\NotifyException
     */
    private function getUser($id)
    {
        $userService = new User();
        $userService
            ->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());

        if (($user = $userService->get($id)) != false) {
            return $user;
        } else {
            throw new NotifyException("User [{$id}] not found");
        }
    }

    /**
     * @param $id
     * @return \Stjornvisi\Service\Group
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getGroup($id)
    {
        $groupService = new GroupService();
        $groupService
            ->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());

        if (($group = $groupService->get($id)) != false) {
            return $group;
        } else {
            throw new NotifyException("Group [{$id}] not found");
        }
    }

    /**
     * @return \PDO
     */
    protected function getDataSourceDriver()
    {
        if ($this->pdo === null) {
            $this->pdo = new \PDO(
                $this->dataStore['dns'],
                $this->dataStore['user'],
                $this->dataStore['password'],
                $this->dataStore['options']
            );
        }
        return $this->pdo;
    }

    /**
     * Close connection, or simply set the
     * instance to NULL.
     *
     * @return $this
     */
    protected function closeDataSourceDriver()
    {
        $this->pdo = null;
        return $this;
    }
}
