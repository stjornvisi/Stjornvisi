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
use Stjornvisi\Service\Event;
use Stjornvisi\Service\User;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use Zend\EventManager\EventManager;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\EventManager\EventManagerInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Handler to send attendance message to users after they
 * have registered to event.
 *
 * @package Stjornvisi\Notify
 */
class Attend implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface
{
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
     * @param \stdClass $data
     * @return $this
     * @throws NotifyException
     */
    public function setData($data)
    {
        $this->params = $data->data;

        if (!property_exists($this->params, 'event_id')) {
            throw new NotifyException('Missing data:event_id');
        }
        if (!property_exists($this->params, 'recipients')) {
            throw new NotifyException('Missing data:recipients');
        }
        if (!property_exists($this->params, 'type')) {
            throw new NotifyException('Missing data:type');
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
        //DATA-OBJECTS
        //  get data-objects from persistence layer.
        $eventObject = $this->getEvent($this->params->event_id);
        $userObject = $this->getUser($this->params->recipients);

        //VIEW
        //	create and configure view
        $child = new ViewModel(array(
            'user' => $userObject,
            'event' => $eventObject
        ));
        $child->setTemplate(($this->params->type)?'attend':'unattend');

        $layout = new ViewModel();
        $layout->setTemplate('layout');
        $layout->addChild($child, 'content');

        $phpRenderer = new PhpRenderer();
        $phpRenderer->setCanRenderTrees(true);

        $resolver = new Resolver\TemplateMapResolver();
        $resolver->setMap(array(
            'layout' => __DIR__ . '/../../../view/layout/email.phtml',
            'attend' => __DIR__ . '/../../../view/email/attending.phtml',
            'unattend' => __DIR__ . '/../../../view/email/un-attending.phtml',
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

        //MESSAGE
        //  create and configure message.
        $message = new Mail();
        $message->body = $phpRenderer->render($layout);
        $message->email = $userObject->email;
        $message->name = $userObject->name;
        $message->subject = ($this->params->type)
            ? "Þú hefur skráð þig á viðburðinn: {$eventObject->subject}"
            : "Þú hefur afskráð þig af viðburðinum: {$eventObject->subject}";

        //MAIL
        //	now we want to send this to the user/quest via e-mail
        //	so we try to connect to Queue and send a message
        //	to mail_queue
        try {
            $connection = $this->queueFactory->createConnection();
            $channel = $connection->channel();
            $channel->queue_declare('mail_queue', false, true, false, false);
            $msg = new AMQPMessage($message->serialize(), ['delivery_mode' => 2]);

            $this->logger->info(
                "{$userObject->email} is ".
                ($this->params->type?'':'not ').
                "attending {$eventObject->subject}"
            );

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

            $eventObject = null;
            $userObject = null;
            $this->closeDataSourceDriver();
        }
        return $this;
    }

    /**
     * Set Queue factory
     * @param QueueConnectionFactoryInterface $factory
     * @return Attend
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
     * Get the recipient.
     *
     * @param $recipient
     * @return bool|null|object|\stdClass
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getUser($recipient)
    {
        if (!$recipient) {
            throw new NotifyException('No recipient provided');
        }

        $user = new User();
        $user->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());

        $userObject = null;

        //USER
        //	user can be in the system or he can be
        //	a guest, we have to prepare for both.
        if (is_numeric($recipient)) {
            $userObject = $user->get($recipient);
            if (!$userObject) {
                throw new NotifyException("User [{$recipient}] not found");
            }
        } else {
            $userObject = (object)array(
                'name' => $recipient->name,
                'email' => $recipient->email
            );
        }

        return $userObject;
    }

    /**
     * @param $event_id
     * @return bool|\stdClass|Event
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getEvent($event_id)
    {
        $event = new Event();
        $event->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());
        if (($event = $event->get($event_id)) != false) {
            return $event;
        } else {
            throw new NotifyException("Event [{$event_id}] not found");
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
