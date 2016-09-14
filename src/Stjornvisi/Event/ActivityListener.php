<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/31/14
 * Time: 9:35 PM
 */

namespace Stjornvisi\Event;

use Stjornvisi\Lib\QueueConnectionFactory;
use Stjornvisi\Module;
use Stjornvisi\Notify\Message\Mail;
use Stjornvisi\Service\Company;
use Stjornvisi\Service\Event;
use Stjornvisi\Service\News;
use Stjornvisi\Service\User;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Psr\Log\LoggerInterface;
use Zend\EventManager\EventInterface;

use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class ActivityListener
 * @package Stjornvisi\Event
 * @todo major refactoring
 */
class ActivityListener extends AbstractListenerAggregate implements QueueConnectionAwareInterface
{
    /** @var  \Psr\Log\LoggerInterface; */
    private $logger;

    /** @var \Stjornvisi\Lib\QueueConnectionFactoryInterface  */
    private $queueFactory;

    /**
     * Create this Aggregate Event Listener.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     *
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            array('create','update','delete'),
            array($this, 'log')
        );
    }

    /**
     * Actually do the logging.
     *
     * @param EventInterface $event
     * @todo this requires a major rewrite
     */
    public function log(EventInterface $event)
    {
        $target = $event->getTarget();
        $recipient = [
            (object)['name'=>'Stjónvísi', 'address'=>'stjornvisi@stjornvisi.is'],
        ];
        $params = $event->getParams();
        $method = isset($params[0])?$params[0]:'';
        $server = Module::getServerUrl();

        if ($target instanceof Event && isset($params['data'])) {
            $data = $params['data'];
            switch ($method) {
                case 'create':
                    $this->send(
                        $recipient,
                        '[Activity]:Viðburður stofnaður',
                        "<p>Viðburður <strong>{$data['subject']}</strong> stofnaður <a href=\"$server/vidburdir/{$data['id']}\">$server/vidburdir/{$data['id']}</a></p>"
                    );
                    break;
                case 'update':
                    $this->send(
                        $recipient,
                        '[Activity]:Viðburður uppfærður',
                        "<p>Viðburður <strong>{$data['subject']}</strong> uppfærður <a href=\"$server/vidburdir/{$data['id']}\">$server/vidburdir/{$data['id']}</p>"
                    );
                    break;
                case 'delete':
                    $this->send(
                        $recipient,
                        '[Activity]:Viðburði eytt',
                        "<p>Viðburði <strong>{$data['subject']}</strong> eytt</p>"
                    );
                    break;
                default:
                    break;
            }
        } else if ($target instanceof News && isset($params['data'])) {
            $data = $params['data'];
            switch ($method) {
                case 'create':
                    $this->send(
                        $recipient,
                        '[Activity]:Frétt stofnuð',
                        "<p>Frétt <strong>{$data['title']}</strong> stofnuð <a href=\"$server/frettir/{$data['id']}\">$server/frettir/{$data['id']}</a></p>"
                    );
                    break;
                case 'update':
                    $this->send(
                        $recipient,
                        '[Activity]:Frétt uppfærð',
                        "<p>Frétt <strong>{$data['title']}</strong> uppfærður <a href=\"$server/frettir/{$data['id']}\">$server/frettir/{$data['id']}</p>"
                    );
                    break;
                case 'delete':
                    $this->send(
                        $recipient,
                        '[Activity]:Frétt eytt',
                        "<p>Frétt <strong>{$data['title']}</strong> eytt</p>"
                    );
                    break;
                default:
                    break;
            }
        } else if ($target instanceof Company && isset($params['data'])) {
            $data = $params['data'];
            switch ($method) {
                case 'create':
                    $this->send(
                        $recipient,
                        '[Activity]:Fyrirtæki stofnuð',
                        "<p>Fyrirtæki <strong>{$data['name']}</strong> stofnuð <a href=\"$server/fyrirtaeki/{$data['id']}\">$server/fyrirtaeki/{$data['id']}</a></p>"
                    );
                    break;
                case 'update':
                    $this->send(
                        $recipient,
                        '[Activity]:Fyrirtæki uppfært',
                        "<p>Fyrirtæki <strong>{$data['name']}</strong> uppfært <a href=\"$server/fyrirtaeki/{$data['id']}\">$server/fyrirtaeki/{$data['id']}</p>"
                    );
                    break;
                case 'delete':
                    $this->send(
                        $recipient,
                        '[Activity]:Fyrirtæki eytt',
                        "<p>Fyrirtæki <strong>{$data['name']}</strong> eytt</p>"
                    );
                    break;
                default:
                    break;
            }
        } elseif ($target instanceof User && isset($params['data'])) {
            $data = $params['data'];
            switch ($method) {
                case 'create':
                    $this->send(
                        $recipient,
                        '[Activity]:Notandi stofnaður',
                        "<p>Notandi <strong>{$data['name']}</strong> stofnaður <a href=\"$server/notandi/{$data['id']}\">$server/notandi/{$data['id']}</a></p>"
                    );
                    break;
                default:
                    break;
            }
        }

    }

    private function send($recipients, $subject, $body)
    {
        $channel = false;
        $connection = false;
        try {
            $connection = $this->queueFactory->createConnection();
            $channel = $connection->channel();
            $queue = QueueConnectionFactory::getMailQueueName();
            $channel->queue_declare($queue, false, true, false, false);

            foreach ($recipients as $recipient) {
                $message = new Mail();
                $message->name = $recipient->name;
                $message->email = $recipient->address;
                $message->subject = $subject;
                $message->body = $body;

                $msg = new AMQPMessage($message->serialize(), ['delivery_mode' => 2]);

                $channel->basic_publish($msg, '', $queue);
            }


        } catch (\Exception $e) {
            while ($e) {
                $this->logger->critical(
                    get_class($this) . ":send says: {$e->getMessage()}",
                    $e->getTrace()
                );
                $e = $e->getPrevious();
            }

        } finally {
            if (isset($channel) && $channel) {
                $channel->close();
            }
            if(isset($connection) && $connection){
                $connection->close();
            }
        }
    }

    /**
     * Set Queue factory
     * @param QueueConnectionFactoryInterface $factory
     * @return mixed
     */
    public function setQueueConnectionFactory(QueueConnectionFactoryInterface $factory)
    {
        $this->queueFactory = $factory;
    }
}
