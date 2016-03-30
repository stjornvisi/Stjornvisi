<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 16/04/15
 * Time: 7:19 PM
 */

namespace Stjornvisi\Notify;

use \DateTime;
use \DateInterval;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;
use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\Event as EventService;
use Stjornvisi\Service\User as UserService;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\TemplateMapResolver;

class Digest implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface
{
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
     * @param $config
     */
    public function setDateStore($config)
    {
        $this->dataStore = $config;
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
     * Set a logger object to monitor the handler.
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
     * The data to be passed to the mail process.
     *
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        return $this;
    }

    /**
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        //ID
        //  create an ID for this digest
        $emailId = $this->getHash();

        //TIME RANGE
        //	calculate time range and create from and
        //	to date objects for the range.
        $from = new DateTime();
        $from->add(new DateInterval('P1D'));
        $to = new DateTime();
        $to->add(new DateInterval('P8D'));

        $this->logger->info("Queue Service says: Fetching upcoming events");

        //EVENTS
        //  fetch all events
        $events = $this->getEvents($from, $to);

        //NO EVENTS
        //	if there are no events to publish, then it's no need
        //	to keep on processing this script
        if (count($events) == 0) {
            $this->logger->info("Digest, no events registered, stop");
            return $this;
        } else {
            $this->logger->info("Digest, ".count($events)." events registered.");
        }

        //USERS
        //	get all users who want to know
        //	about the upcoming events.
        $users = $this->getUsers();
        $this->logger->info("Digest, ".count($users)." user will get email ");


        //VIEW
        //	create and configure view
        $child = new ViewModel(array(
            'events' => $events,
            'from' => $from,
            'to' => $to
        ));
        $child->setTemplate('news-digest');


        $layout = new ViewModel();
        $layout->setTemplate('layout');
        $layout->addChild($child, 'content');

        $phpRenderer = new PhpRenderer();
        $phpRenderer->setCanRenderTrees(true);

        $resolver = new TemplateMapResolver();
        $resolver->setMap(array(
            'layout' => __DIR__ . '/../../../view/layout/email.phtml',
            'news-digest' => __DIR__ . '/../../../view/email/news-digest.phtml',
        ));
        $phpRenderer->setResolver($resolver);

        //QUEUE
        //	try to connect to Queue and send messages to it.
        //	this will try to send messages to mail_queue, that will
        //	send them on it's way via a MailTransport
        try {
            $connection = $this->queueFactory->createConnection();
            $channel = $connection->channel();
            $channel->queue_declare('mail_queue', false, true, false, false);

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

                $result = new Mail();
                $result->name = $user->name;
                $result->email = $user->email;
                $result->subject = "Vikan framundan | {$from->format('j. n.')} - {$to->format('j. n. Y')}";
                $result->body = $phpRenderer->render($layout);
                $result->id = $emailId;
                $result->user_id = md5((string)$emailId . $user->email);
                $result->type = 'Digest';
                $result->parameters = 'allir';
                $result->test = false;

                $msg = new AMQPMessage($result->serialize(), ['delivery_mode' => 2]);

                $channel->basic_publish($msg, '', 'mail_queue');
                $this->logger->debug("Queue Service says: Fetching users who want upcoming events, {$user->email} in queue ");
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
     * Generate an unique hash for this
     * action.
     *
     * @return string
     */
    private function getHash()
    {
        return md5(time() + rand(0, 1000));
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

    /**
     * @param DateTime $from
     * @param DateTime $to
     * @return array
     * @throws \Stjornvisi\Service\Exception
     */
    private function getEvents(DateTime $from, DateTime $to)
    {
        $eventService = new EventService();
        $eventService->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());

        return $eventService->getRange($from, $to);
    }

    /**
     * @return array
     * @throws \Stjornvisi\Service\Exception
     */
    private function getUsers()
    {
        $userService = new UserService();
        $userService->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());
        return $userService->fetchAllForEmail('email_event_upcoming');
    }
}
