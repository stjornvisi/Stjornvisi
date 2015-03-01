<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/31/14
 * Time: 9:35 PM
 */

namespace Stjornvisi\Event;


use Stjornvisi\Service\Company;
use Stjornvisi\Service\Event;
use Stjornvisi\Service\News;
use Stjornvisi\Service\User;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\LoggerInterface;
use Zend\EventManager\EventInterface;

use Stjornvisi\Lib\QueueConnectionAwareInterface;
use Stjornvisi\Lib\QueueConnectionFactoryInterface;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class ActivityListener
 * @package Stjornvisi\Event
 * @todo major refactoring
 */
class ActivityListener extends AbstractListenerAggregate implements QueueConnectionAwareInterface {

	/** @var \Zend\Log\LoggerInterface  */
	private $logger;

	/** @var \Stjornvisi\Lib\QueueConnectionFactoryInterface  */
	private $queueFactory;

	/**
	 * Create this Aggregate Event Listener.
	 *
	 * @param LoggerInterface $logger
	 */
	public function __construct( LoggerInterface $logger ){
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
    public function attach(EventManagerInterface $events){
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
	public function log(EventInterface $event){
		$target = $event->getTarget();
		$recipient = array('name'=>'Stjónvísi', 'address'=>'stjornvisi@stjornvisi.is');
		$params = $event->getParams();
		$method = isset($params[0])?$params[0]:'';

		if( $target instanceof Event && isset($params['data']) ){
			$data = $params['data'];
			switch( $method ){
				case 'create':
					$this->send(
						$recipient,
						'[Activity]:Viðburður stofnaður',
						"<p>Viðburður <strong>{$data['subject']}</strong> stofnaður <a href=\"http://stjornvisi.is/vidburdir/{$data['id']}\">http://stjornvisi.is/vidburdir/{$data['id']}</a></p>"
					);
					break;
				case 'update':
					$this->send(
						$recipient,
						'[Activity]:Viðburður uppfærður',
						"<p>Viðburður <strong>{$data['subject']}</strong> uppfærður <a href=\"http://stjornvisi.is/vidburdir/{$data['id']}\">http://stjornvisi.is/vidburdir/{$data['id']}</p>"
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
		}else if( $target instanceof News && isset($params['data'])  ){
			$data = $params['data'];
			switch( $method ){
				case 'create':
					$this->send(
						$recipient,
						'[Activity]:Frétt stofnuð',
						"<p>Frétt <strong>{$data['title']}</strong> stofnuð <a href=\"http://stjornvisi.is/frettir/{$data['id']}\">http://stjornvisi.is/frettir/{$data['id']}</a></p>"
					);
					break;
				case 'update':
					$this->send(
						$recipient,
						'[Activity]:Frétt uppfærð',
						"<p>Frétt <strong>{$data['title']}</strong> uppfærður <a href=\"http://stjornvisi.is/frettir/{$data['id']}\">http://stjornvisi.is/frettir/{$data['id']}</p>"
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
		}else if( $target instanceof Company && isset($params['data'])  ){
			$data = $params['data'];
			switch( $method ){
				case 'create':
					$this->send(
						$recipient,
						'[Activity]:Fyrirtæki stofnuð',
						"<p>Fyrirtæki <strong>{$data['name']}</strong> stofnuð <a href=\"http://stjornvisi.is/fyrirtaeki/{$data['id']}\">http://stjornvisi.is/fyrirtaeki/{$data['id']}</a></p>"
					);
					break;
				case 'update':
					$this->send(
						$recipient,
						'[Activity]:Fyrirtæki uppfært',
						"<p>Fyrirtæki <strong>{$data['name']}</strong> uppfært <a href=\"http://stjornvisi.is/fyrirtaeki/{$data['id']}\">http://stjornvisi.is/fyrirtaeki/{$data['id']}</p>"
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
		}elseif ( $target instanceof User && isset($params['data'])  ){
			$data = $params['data'];
			switch( $method ){
				case 'create':
					$this->send(
						$recipient,
						'[Activity]:Notandi stofnaður',
						"<p>Notandi <strong>{$data['name']}</strong> stofnaður <a href=\"http://stjornvisi.is/notandi/{$data['id']}\">http://stjornvisi.is/notandi/{$data['id']}</a></p>"
					);
					break;
				default:
					break;
			}
		}

	}

	private function send( $recipient, $subject, $body ){
		$channel = false;
		$connection = false;
		try{
			$connection = $this->queueFactory->createConnection();
			$channel = $connection->channel();
			$channel->queue_declare('mail_queue', false, true, false, false);
			$msg = new AMQPMessage( json_encode(array(
					'recipient' => $recipient,
					'subject' => $subject,
					'body' => $body
				)),
				array('delivery_mode' => 2) # make message persistent
			);

			$channel->basic_publish($msg, '', 'mail_queue');


		}catch (\Exception $e){
			$this->logger->warn(get_class($this) . ":send says: {$e->getMessage()}");
		}finally{
			if( $channel ){
				$channel->close();
			}
			if( $connection ){
				$connection->close();
			}
		}
	}

	/**
	 * Set Queue factory
	 * @param QueueConnectionFactoryInterface $factory
	 * @return mixed
	 */
	public function setQueueConnectionFactory( QueueConnectionFactoryInterface $factory ){
		$this->queueFactory = $factory;
	}
}