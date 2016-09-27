<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 28/09/14
 * Time: 22:42
 */

namespace Stjornvisi\Notify;

use Psr\Log\LoggerInterface;

use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactory;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;
use Stjornvisi\Module;
use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\User;

use Stjornvisi\View\Helper\Paragrapher;
use Zend\EventManager\EventManager;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\EventManager\EventManagerInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Emails sent from an event to all or attendees.
 *
 * @package Stjornvisi\Notify
 */
class Event implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface
{
    /**
     * @var \stdClass
     */
    private $params;

    /**
     * @var  \Psr\Log\LoggerInterface;
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
     * @param $data
     * @return $this
     * @throws NotifyException
     */
    public function setData($data)
    {
        $this->params = $data->data;
        if (!property_exists($this->params, 'user_id')) {
            throw new NotifyException('Missing data:user_id');
        }
        if (!property_exists($this->params, 'recipients')) {
            throw new NotifyException('Missing data:recipients');
        }
        if (!property_exists($this->params, 'test')) {
            throw new NotifyException('Missing data:test');
        }
        if (!property_exists($this->params, 'body')) {
            throw new NotifyException('Missing data:body');
        }
        if (!property_exists($this->params, 'subject')) {
            throw new NotifyException('Missing data:subject');
        }
        if (!property_exists($this->params, 'event_id')) {
            throw new NotifyException('Missing data:event_id');
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
        $emailId = $this->getEmailId();
        $event = $this->getEvent($this->params->event_id);
        $users = $this->getUser(
            $this->getGroupsFromEvent($event),
            $event->id,
            $this->params->user_id,
            $this->params->recipients,
            $this->params->test
        );

        $this->logger->info(
            (count($users)) . " user will get an email" .
            "in connection with event {$event->subject}:{$event->id}"
        );

        //VIEW
        //	create and configure view
        $child = new ViewModel(array(
            'user' => null,
            'event' => $event,
            'body' => call_user_func(new Paragrapher(), $this->params->body)
        ));
        $child->setTemplate('event');

        $layout = new ViewModel();
        $layout->setTemplate('layout');
        $layout->addChild($child, 'content');

        $phpRenderer = new PhpRenderer();
        $phpRenderer->setCanRenderTrees(true);

        $resolver = new Resolver\TemplateMapResolver();
        $resolver->setMap(array(
            'layout' => __DIR__ . '/../../../view/layout/email.phtml',
            'event' => __DIR__ . '/../../../view/email/event.phtml',
        ));
        $phpRenderer->setResolver($resolver);


        //CONNECT TO QUEUE
        //	try to connect to RabbitMQ
        try {
            $connection = $this->queueFactory->createConnection();
            $channel = $connection->channel();
            $queue = QueueConnectionFactory::getMailQueueName();
            $channel->queue_declare($queue, false, true, false, false);

            //FOR EVER USER
            //	for every user: render email template, create message object and
            //	send to mail-queue
            foreach ($users as $user) {
                $child->setVariable('user', $user);
                foreach ($layout as $child) {
                    $child->setOption('has_parent', true);
                    $result  = $phpRenderer->render($child);
                    $child->setOption('has_parent', null);
                    $capture = $child->captureTo();
                    if (!empty($capture)) {
                        $layout->setVariable($capture, $result);
                    }
                }

                $message = new Mail();
                $message->name = $user->name;
                $message->email = $user->email;
                $message->subject = $this->params->subject;
                $message->body = $phpRenderer->render($layout);
                $message->id = $emailId;
                $message->user_id = md5((string)$emailId . $user->email);
                $message->type = 'Event';
                $message->entity_id = $event->id;
                $message->parameters = $this->params->recipients;
                $message->test = $this->params->test;

                $msg = new AMQPMessage($message->serialize(), ['delivery_mode' => 2]);

                $this->logger->info(
                    " {$user->email} will get an email" .
                    "in connection with event {$event->subject}:{$event->id}"
                );

                $channel->basic_publish($msg, '', $queue);
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

            $users = null;
            $event = null;
            $this->closeDataSourceDriver();
        }
        return $this;
    }

    /**
     * Set Queue factory.
     *
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
     * @param array $groups
     * @param $eventId
     * @param $userId
     * @param $recipients
     * @param $test
     * @return \stdClass[]
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getUser(array $groups, $eventId, $userId, $recipients, $test)
    {
        $user = new User();
        $user->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());

        //TEST
        //	this is just a test message so we send it just to the user in question
        if ($test || Module::isStaging()) {
            if (($result = $user->get($userId)) != false) {
                return [$result];
            } else {
                throw new NotifyException("Sender not found");
            }

        //REAL
        //	this is the real thing.
        //	If $groupIds is NULL/empty, this this is a Stjornvisi Event and then
        //	we just fetch all valid users in the system.
        } else {
            $users = ($recipients == 'allir')
                ? (empty($groups))
                    ? $user->fetchAllForEmail()
                    : $user->fetchUserEmailsByGroup($groups, null, false, 'email_event_all')
                : $user->fetchUserEmailsByEvent($eventId) ;
            if (empty($users)) {
                throw new NotifyException("No users found for event notification");
            } else {
                return $users;
            }
        }
    }

    /**
     * @param int $eventId
     * @return bool|\stdClass|\Stjornvisi\Service\Event
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getEvent($eventId)
    {
        $event = new \Stjornvisi\Service\Event();
        $event->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());

        //EVENT
        //	first of all, find the event in question
        if (($event = $event->get($eventId)) != false) {
            return $event;
        } else {
            throw new NotifyException("Event [{$eventId}] not found");
        }
    }

    /**
     * Get unique ID for these emails.
     *
     * @return string
     */
    private function getEmailId()
    {
        return md5(time() + rand(0, 1000));
    }

    /**
     * Extract just the Group IDs from the event.
     *
     * @param $event
     * @return array
     */
    private function getGroupsFromEvent($event)
    {
        return array_map(
            function ($i) {
                return $i->id;
            },
            $event->groups
        );
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
