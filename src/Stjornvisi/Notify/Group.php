<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

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
use Psr\Log\LoggerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Email sent from a group to all or board.
 *
 * @package Stjornvisi\Notify
 */
class Group implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface
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
        if (!property_exists($this->params, 'recipients')) {
            throw new NotifyException('Missing data:recipients');
        }
        if (!property_exists($this->params, 'test')) {
            throw new NotifyException('Missing data:test');
        }
        if (!property_exists($this->params, 'sender_id')) {
            throw new NotifyException('Missing data:sender_id');
        }
        if (!property_exists($this->params, 'group_id')) {
            throw new NotifyException('Missing data:group_id');
        }
        if (!property_exists($this->params, 'body')) {
            throw new NotifyException('Missing data:body');
        }
        if (!property_exists($this->params, 'subject')) {
            throw new NotifyException('Missing data:subject');
        }
        return $this;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this|NotifyInterface
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Run the handler.
     *
     * @return $this
     * @throws NotifyException
     */
    public function send()
    {
        $emailId = $this->getHash();

        $users = $this->getUsers(
            $this->params->recipients,
            $this->params->test,
            $this->params->sender_id,
            $this->params->group_id
        );
        $group = $this->getGroup($this->params->group_id);

        $this->logger->info("Group-email in " . ( $this->params->test?'':'none' ) . " test mode");

        //MAIL
        //	now we want to send this to the user/quest via e-mail
        //	so we try to connect to Queue and send a message
        //	to mail_queue
        try {
            //QUEUE
            //	create and configure queue
            $connection = $this->queueFactory->createConnection();
            $channel = $connection->channel();
            $queue = QueueConnectionFactory::getMailQueueName();
            $channel->queue_declare($queue, false, true, false, false);

            //VIEW
            //	create and configure view
            $child = new ViewModel(array(
                'user' => null,
                'group' => $group,
                'body' => call_user_func(new Paragrapher(), $this->params->body)
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
                'script' => __DIR__ . '/../../../view/email/group-letter.phtml',
            ));
            $phpRenderer->setResolver($resolver);

            //FOR EVERY USER
            //	for every user, render mail-template
            //	and send to mail-queue
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
                $result->subject = $this->params->subject;
                $result->body = $phpRenderer->render($layout);
                $result->user_id = md5((string)$emailId . $user->email);
                $result->id = $emailId;
                $result->type = 'Event';
                $result->entity_id = $group->id;
                $result->parameters = $this->params->recipients;
                $result->test = $this->params->test;


                $msg = new AMQPMessage($result->serialize(), ['delivery_mode' => 2]);

                $this->logger->info("Groupmail to user:{$user->email}, group:{$group->name_short}");

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

            $users  = null;
            $group  = null;
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
     * @param string $recipients
     * @param bool $test
     * @param int $sender_id
     * @param int $group_id
     * @return \stdClass[]
     * @throws \Stjornvisi\Service\Exception
     */
    private function getUsers($recipients, $test, $sender_id, $group_id)
    {
        $userService    = new User();
        $userService->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());

        //ALL OR FORMEN
        //	send to all members of group or forman
        $types = ($recipients == 'allir')
            ? null  //everyone
            : [1, 2] ; // all managers

        //TEST OR REAL
        //	if test, send ony to sender, else to all
        return ($test || Module::isStaging())
            ? [$userService->get($sender_id)]
            : $userService->fetchUserEmailsByGroup([$group_id], $types);
    }

    /**
     * @param $group_id
     * @return bool|\stdClass
     * @throws NotifyException
     * @throws \Stjornvisi\Service\Exception
     */
    private function getGroup($group_id)
    {
        $groupService   = new \Stjornvisi\Service\Group();
        $groupService->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());

        if (($group = $groupService->get($group_id))!= false) {
            return $group;
        } else {
            throw new NotifyException("Group [{$group_id}] not found");
        }
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
        if ($this->pdo === null) { # TODO use the service locator
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
