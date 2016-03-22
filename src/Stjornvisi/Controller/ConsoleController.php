<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 25/03/14
 * Time: 11:20
 */

namespace Stjornvisi\Controller;

use CanGelis\PDF\PDF;
use League\Flysystem\Adapter\Local;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPExceptionInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Stjornvisi\Lib\QueueConnectionFactory;
use Stjornvisi\Mail\Attacher;
use Stjornvisi\Notify\Message\Mail as NotifyMailMessage;
use Stjornvisi\Notify\NotifyInterface;
use Zend\Console\Request as ConsoleRequest;
use Zend\Console\Request as Request;
use Zend\Mail\Message;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Zend\View\Resolver\TemplateMapResolver;

/**
 * Class ConsoleController
 * @package Stjornvisi\Controller
 * @method Request getRequest()
 */
class ConsoleController extends AbstractActionController
{
    /**
     * Add to mail queue all e-mails that
     * will go out about the upcoming event week.
     * <code>
     * $ php index.php queue events
     * </code>
     */
    public function queueUpComingEventsAction()
    {
        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $sm = $this->getServiceLocator();
        /** @var $logger \Zend\Log\Logger|object */
        $logger = $sm->get('Logger');

        try {
            $logger->info("UpComingEvents: Started");
            $process = $sm->get('Stjornvisi\Notify\Digest');
            /** @var $process \Stjornvisi\Notify\Digest */
            $process->send();
            $logger->info("UpComingEvents: Done");

        } catch (\Exception $e) {
            while ($e) {
                $logger->critical("Exception in UpComingEvents: {$e->getMessage()}", $e->getTrace());
                $e = $e->getPrevious();
            }
            exit(1);
        }
        exit(0);
    }

    /**
     * This guy is listening for notifications from the application layer.
     *
     * The system (mostly the Controllers) will issue a notify event that
     * this process will listen for. Every notice is special and here they are
     * decrypted and a special handler is selected for ech one. Most of these
     * handlers will do some sort of data manipulation/aggregation and then send them
     * on to the mail-queue where they are sent via SMTP, but anything can happen
     * and they could relay the message to Facebook for all we care.
     */
    public function notifyAction()
    {
        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $appEnv = getenv('APPLICATION_ENV') ?: 'production';

        $sm = $this->getServiceLocator();
        /** @var \Zend\Log\Logger */
        $logger = $sm->get('Logger');

        try {
            /** @var QueueConnectionFactory $connectionFactory */
            $connectionFactory = $sm->get('Stjornvisi\Lib\QueueConnectionFactory');
            $connection = $connectionFactory->createConnection();
            $channel = $connection->channel();

            $channel->queue_declare('notify_queue', false, true, false, false);

            $logger->info("Notice Listener started, Waiting for messages. Environment=$appEnv. To exit press CTRL+C");

            //THE MAGIC
            //  here is where everything happens. the rest of the code
            //  is just connect and disconnect to RabbitMQ
            $callback = function ($msg) use ($logger, $sm) {
                /** @var AMQPMessage $msg */
                /** @var AMQPChannel $msgChannel */
                $msgChannel = $msg->delivery_info['channel'];

                //MESSAGE AND HANDLER
                //  try to decode the JSON string into object
                //  and set up a default handler that does nothing
                $message = json_decode($msg->body);

                $handler = null;

                try {
                    /** @var NotifyInterface $handler */
                    $handler = $sm->get($message->action);
                    $logger->info("Notify message [{$message->action}] obtained");

                    $handler->setData($message);
                    $handler->send();

                    $logger->info("Notify message [{$message->action}] processed");
                    $msgChannel->basic_ack($msg->delivery_info['delivery_tag']);

                } catch (ServiceNotFoundException $e) {
                    $logger->critical("Notify message [{$message->action}] not found", $e->getTrace());
                } catch (\Exception $e) {
                    while ($e) {
                        $logger->critical($e->getMessage(), $e->getTrace());
                        $e = $e->getPrevious();
                    }
                } finally {
                    $handler = null;
                }

            };// end of - MAGIC

            $channel->basic_qos(null, 1, null);
            $channel->basic_consume('notify_queue', '', false, false, false, false, $callback);

            while (count($channel->callbacks)) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();

        } catch (AMQPExceptionInterface $e) {
            $logger->alert("Can't start NotifyQueue: " . get_class($e) . ": {$e->getMessage()}", $e->getTrace());
            exit(1);
        } catch (\Exception $e) {
            while ($e) {
                $logger->critical("Exception in MailQueue: {$e->getMessage()}", $e->getTrace());
                $e = $e->getPrevious();
            }
        }
    }

    /**
     * This is the actual Mail Queue.
     *
     * This is the guy who sends out e-mail. He is listening
     * for messages sent to the RabbitMQ:mail_queue and he will
     * relay them to the SMTP protocol.
     *
     * @throws \RuntimeException
     */
    public function mailAction()
    {
        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (!$request instanceof ConsoleRequest) {
            throw new \RuntimeException('You can only use this action from a console!');
        }

        $appEnv = getenv('APPLICATION_ENV') ?: 'production';

        $sm = $this->getServiceLocator();
        /** @var $logger \Zend\Log\Logger|object */
        $logger = $sm->get('Logger');

        try {
            /** @var QueueConnectionFactory $connectionFactory */
            $connectionFactory = $sm->get('Stjornvisi\Lib\QueueConnectionFactory');
            $connection = $connectionFactory->createConnection();
            $channel = $connection->channel();
            $channel->queue_declare('mail_queue', false, true, false, false);

            $logger->info("Mail Queue started, Waiting for messages. Environment=$appEnv. To exit press CTRL+C");

            //THE MAGIC
            //  here is where everything happens. the rest of the code
            //  is just connect and deconnect to RabbitMQ
            $callback = function ($msg) use ($logger, $sm) {
                /** @var AMQPMessage $msg */
                /** @var AMQPChannel $msgChannel */
                $msgChannel = $msg->delivery_info['channel'];

                $messageObject = new NotifyMailMessage();
                $messageObject->unserialize($msg->body);

                if (!filter_var($messageObject->email, FILTER_VALIDATE_EMAIL)) {
                    $msgChannel->basic_ack($msg->delivery_info['delivery_tag']);
                    $logger->error("Main mailer: Invalid mail address [{$messageObject->email}]");
                    return;
                }

                $logger->debug("Send e-mail to [{$messageObject->email}, {$messageObject->name}]");

                //CREATE / SEND
                //  create a mail message and actually send it
                $message = new Message();
                $message->addTo($messageObject->email, $messageObject->name)
                    ->addFrom('stjornvisi@stjornvisi.is', "Stjórnvísi")
                    ->setSubject($messageObject->subject)
                    ->setBody($messageObject->body)
                    ->setEncoding("UTF-8");

                //ATTACHER
                //  you can read all about what this does in \Stjornvisi\Mail\Attacher
                //  but basically what this does is: convert a simple html string into a
                //  multy-part mime object with embedded attachments.
                $trackerId = ($messageObject->user_id)
                    ? $messageObject->user_id
                    : ''; //TODO why do I need this?
                $attacher = new Attacher($message);
                $message = $attacher->parse("http://tracker.stjornvisi.is/spacer.gif?id={$trackerId}");

                //DEBUG MODE
                //  process started with --debug flag
                if ($this->getRequest()->getParam('debug', false)) {
                    $logger->debug("Mailer:mailAction " . print_r($messageObject, true));
                    //TRACE MODE
                    //  process started with --trace flag
                } elseif ($this->getRequest()->getParam('trace', false)) {
                    $logger->debug("Mailer:mailAction " . $message->toString());
                    //NORMAL MODE
                    //  process started in normal mode. e-mail will be sent
                } else {
                    try {
                        $transport = $sm->get('MailTransport');
                        /** @var $transport \Zend\Mail\Transport\Smtp */

                        if (method_exists($transport, 'getConnection') && ($protocol = $transport->getConnection())) {
                            if ($protocol->hasSession()) {
                                $transport->send($message);
                            } else {
                                $protocol->connect();
                                $protocol->helo('localhost.localdomain');
                                $protocol->rset();
                                $transport->send($message);
                            }

                            $protocol->quit();
                            $protocol->disconnect();
                        } else {
                            $transport->send($message);
                        }

                        $msgChannel->basic_ack($msg->delivery_info['delivery_tag']);

                        if ($messageObject->test) {
                            $logger->debug("This is just a test e-mail notofcation -- we will ignore it");
                        } else {
                            try {
                                $emailService = $sm->get('Stjornvisi\Service\Email');
                                /** @var $emailService \Stjornvisi\Service\Email */
                                $emailService->create(array(
                                    'subject' => $messageObject->subject,
                                    'body' => $messageObject->body,
                                    'hash' => $messageObject->id,
                                    'user_hash' => $messageObject->user_id,
                                    'type' => $messageObject->type,
                                    'entity_id' => $messageObject->entity_id,
                                    'params' => $messageObject->parameters,
                                ));
                                $emailService = null;
                            } catch (\Exception $e) {
                                while ($e) {
                                    $logger->critical($e->getMessage(), $e->getTrace());
                                    $e = $e->getPrevious();
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        while ($e) {
                            $logger->critical($e->getMessage(), $e->getTrace());
                            $e = $e->getPrevious();
                        }
                    }
                }
            };// end of - MAGIC

            $channel->basic_qos(null, 1, null);
            $channel->basic_consume('mail_queue', '', false, false, false, false, $callback);

            while (count($channel->callbacks)) {
                $channel->wait();
            }

            $channel->close();
            $connection->close();

        } catch (AMQPExceptionInterface $e) {
            $logger->alert("Can't start MailQueue: " . get_class($e) . ": {$e->getMessage()}", $e->getTrace());
            exit(1);
        } catch (\Exception $e) {
            while ($e) {
                $logger->critical("Exception in MailQueue: {$e->getMessage()}", $e->getTrace());
                $e = $e->getPrevious();
            }

        }
    }

    /**
     * Export the router as a MARKDOWN list
     */
    public function routerAction()
    {
        $config = $this->getServiceLocator()->get('Config');
        $this->printer($config['router']['routes']);
    }

    /**
     * @param array $value
     * @param string $indent
     */
    private function printer($value, $indent = "")
    {
        foreach ($value as $item) {
            echo $indent .
                '* ' . $item['options']['route'] .
                " { _{$item['options']['defaults']['controller']}::{$item['options']['defaults']['action']}_ }" .
                PHP_EOL;
            if (isset($item['child_routes'])) {
                $this->printer($item['child_routes'], ($indent . "    "));
            }
        }
    }

    public function pdfAction()
    {
        $sm = $this->getServiceLocator();
        $companyService = $sm->get('Stjornvisi\Service\Company');
        /** @var  $companyDAO \Stjornvisi\Service\Company */
        $userService = $sm->get('Stjornvisi\Service\User');
        /** @var  $companyDAO \Stjornvisi\Service\User */

        $company = $companyService->get(14);

        array_walk($company->members, function ($member) use ($userService) {
            $attendance = $userService->attendance($member->id);
            $member->attendance = (count($attendance) <= 2)
                ? $attendance
                : array_slice($attendance, -2, 2, false);
        });

        $layout = new ViewModel(array(
            'company' => $company
        ));
        $layout->setTemplate('script');

        $phpRenderer = new PhpRenderer();
        $phpRenderer->setCanRenderTrees(true);

        $resolver = new TemplateMapResolver();
        $resolver->setMap(array(
            'script' => __DIR__ . '/../../../view/pdf/company-report.phtml',
        ));
        $phpRenderer->setResolver($resolver);

        $pdf = new PDF('/usr/local/bin/wkhtmltopdf');

        $pdf->loadHTML($phpRenderer->render($layout))
            ->save("out.pdf", new Local('/Users/einar/Desktop/'), true);

    }
}
