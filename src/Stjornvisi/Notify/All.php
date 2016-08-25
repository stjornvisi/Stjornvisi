<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 26/09/14
 * Time: 15:31
 */

namespace Stjornvisi\Notify;

use Stjornvisi\Lib\QueueConnectionFactory;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;
use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Service\User;
use Stjornvisi\View\Helper\Paragrapher;
use Stjornvisi\Notify\Message\Mail as MailMessage;

use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Psr\Log\LoggerInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Handler to notify everyone in the system. There is no way for a user
 * not to get message from this handler.
 *
 * Currently this handler only sends out e-mail messages.
 *
 * This will transcend all Group config. Only
 * Admin can do this.
 *
 * @package Stjornvisi\Notify
 */
class All implements NotifyInterface, QueueConnectionAwareInterface, DataStoreInterface, NotifyEventManagerAwareInterface
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
     * The data to be passed to the mail process.
     *
     * @param $data
     * @return $this
     * @throws NotifyException
     */
    public function setData($data)
    {
        $this->params = $data->data;
        if (!property_exists($this->params, 'subject')) {
            throw new NotifyException('Missing data:subject');
        }
        if (!property_exists($this->params, 'recipients')) {
            throw new NotifyException('Missing data:recipients');
        }
        if (!property_exists($this->params, 'sender_id')) {
            throw new NotifyException('Missing data:sender_id');
        }
        if (!property_exists($this->params, 'test')) {
            throw new NotifyException('Missing data:test');
        }
        if (!property_exists($this->params, 'body')) {
            throw new NotifyException('Missing data:body');
        }
        return $this;
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
     * Run the handler.
     *
     * @return $this|NotifyInterface
     * @throws NotifyException
     */
    public function send()
    {
        $emailId = $this->getHash();
        $users = $this->getUsers($this->params->sender_id, $this->params->recipients, $this->params->test);
        $this->logger->info("Notify All ({$this->params->recipients})");

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
                'body' =>  call_user_func(new Paragrapher(), $this->params->body)
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
                'script' => __DIR__ . '/../../../view/email/letter.phtml',
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

                $result = new MailMessage();
                $result->name = $user->name;
                $result->email = $user->email;
                $result->subject = $this->params->subject;
                $result->body = $phpRenderer->render($layout);
                $result->id = $emailId;
                $result->user_id = md5((string)$emailId . $user->email);
                $result->entity_id = null;
                $result->type = 'All';
                $result->parameters = $this->params->recipients;
                $result->test = $this->params->test;

                $msg = new AMQPMessage($result->serialize(), ['delivery_mode' => 2]);

                $this->logger->debug("Notify All via e-mail to user:{$user->email}");

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

            $userService = null;
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
     * Find out who is actually getting this
     * e-mail.
     *
     * @param int $sender
     * @param string $recipients
     * @param bool $test
     * @return array
     * @throws \Stjornvisi\Service\Exception
     */
    private function getUsers($sender, $recipients, $test)
    {
        $user = new User();
        $user->setDataSource($this->getDataSourceDriver())
            ->setEventManager($this->getEventManager());

        if ($test) {
            return [$user->get($sender)];
        } else {
           switch ($recipients){
               case "formenn" :
                   $recipientAddresses = $user->fetchAllChairmenForEmail();
                   break;
               case "stjornendur" :
                   $recipientAddresses = $user->fetchAllManagersForEmail();
                   break;
               case "allir" :
                   $recipientAddresses = $user->fetchAllForEmail();
                   break;
               default :
                   $recipientAddresses = [];
           }
        }
        return $recipientAddresses;
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
}
