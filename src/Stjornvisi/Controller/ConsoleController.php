<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 25/03/14
 * Time: 11:20
 */

namespace Stjornvisi\Controller;


use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Progressbar\Adapter\Console;
use Zend\ProgressBar\ProgressBar;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use \DateTime;
use \DateInterval;
use \DirectoryIterator;

use Imagine\Imagick\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Filter\Transformation;
use Imagine\Filter\Basic\Resize;
use Stjornvisi\Lib\Imagine\Square;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;

use Zend\Mail\Message;

class ConsoleController extends AbstractActionController {

	/**
	 * Rebuild search index.
	 *
	 * <code>
	 * $ php index.php search index
	 * </code>
	 *
	 * @throws \RuntimeException
	 * @deprecated
	 */
	public function searchIndexAction(){
		$request = $this->getRequest();

		// Make sure that we are running in a console and the user has not tricked our
		// application into running this action from a public web server.
		if (!$request instanceof ConsoleRequest){
			throw new \RuntimeException('You can only use this action from a console!');
		}

		$sm = $this->getServiceLocator();

		$index = $sm->get('Search\Index\Search');


		//Event
		//	index event entries.
		//
		$counter = 0;
		$eventService = $sm->get('Stjornvisi\Service\Event');
		$events = $eventService->fetchAll();
		$adapter = new Console();

		echo "\nIndexing Event entries\n";
		$progressBar = new ProgressBar($adapter, $counter, count($events));
		$i = new \Stjornvisi\Search\Index\Event();
		foreach($events as $item){
			$i->index($item,$index);
			$progressBar->update(++$counter);
		}
		$index->commit();
		$progressBar->finish();



		//NEWS
		//	index news entries.
		//
		$counter = 0;
		$newsService = $sm->get('Stjornvisi\Service\News');
		$news = $newsService->fetchAll();
		$adapter = new Console();

		echo "\nIndexing News entries\n";
		$progressBar = new ProgressBar($adapter, $counter, count($news));
		$i = new \Stjornvisi\Search\Index\News();
		foreach($news as $item){
			$i->index($item,$index);
			$progressBar->update(++$counter);
		}
		$index->commit();
		$progressBar->finish();




		//Articles
		//	index article entries.
		//
		$counter = 0;
		$articleService = $sm->get('Stjornvisi\Service\Article');
		$articles = $articleService->fetchAll();
		$adapter = new Console();

		echo "\nIndexing News entries\n";
		$progressBar = new ProgressBar($adapter, $counter, count($articles));
		$i = new \Stjornvisi\Search\Index\Article();
		foreach($articles as $item){
			$i->index($item,$index);
			$progressBar->update(++$counter);
		}
		$index->commit();
		$progressBar->finish();



		//Group
		//	index group entries.
		//
		$counter = 0;
		$groupService = $sm->get('Stjornvisi\Service\Group');
		$groups = $groupService->fetchAll();
		$adapter = new Console();

		echo "\nIndexing Group entries\n";
		$progressBar = new ProgressBar($adapter, $counter, count($groups));
		$i = new \Stjornvisi\Search\Index\Group();
		foreach($groups as $item){
			$i->index($item,$index);
			$progressBar->update(++$counter);
		}
		$index->commit();
		$progressBar->finish();

	}


	/**
	 * Add to mail queue all e-mails that
	 * will go out about the upcoming event week.
	 * <code>
	 * $ php index.php queue events
	 * </code>
	 *
	 * @throws \RuntimeException
	 */
	public function queueUpComingEventsAction(){
		$request = $this->getRequest();
		// Make sure that we are running in a console and the user has not tricked our
		// application into running this action from a public web server.
		if (!$request instanceof ConsoleRequest){
			throw new \RuntimeException('You can only use this action from a console!');
		}

		$sm = $this->getServiceLocator();
		$logger = $sm->get('Logger'); /** @var $logger \Zend\Log\Logger */

		$logger->info("Queue Service says: Fetching upcoming events");

		//TIME RANGE
		//	calculate time range and create from and
		//	to date objects for the range.
		$from = new DateTime();
		$from->add(new DateInterval('P1D'));
		$to = new DateTime();
		$to->add(new DateInterval('P8D'));


		//GET EVENTS
		//	get services needed and fetch events that are
		//	in time range
		$eventService = $sm->get('Stjornvisi\Service\Event');
		$userService = $sm->get('Stjornvisi\Service\User');
		$events = $eventService->getRange($from, $to);

		//NO EVENTS
		//	if there are no events to publish, then it's no need
		//	to keep on processing this script
		if( count($events) == 0 ){
			$logger->info("Queue Service says: Fetching upcoming events, no events registered, stop");
			exit(0);
		}else{
			$logger->info("Queue Service says: Fetching upcoming events, ".count($events)." events registered.");
		}


		//USERS
		//	get all users who want to know
		//	about the upcoming events.
		$logger->info("Queue Service says: Fetching users who want upcoming events");
		$users = $userService->getUserMessage();
		$logger->info("Queue Service says: Fetching users who want upcoming events, ".count($users)." user will get email ");

		//VIEW
		//	prep the view and the template engine.
		$renderer = new PhpRenderer();
		$resolver = new Resolver\AggregateResolver();
		$renderer->setResolver($resolver);
		$stack = new Resolver\TemplatePathStack(array(
			'script_paths' => array(
				__DIR__.'/../../../view/email'
			)
		));
		$resolver->attach($stack);
		$model = new ViewModel(array(
			'events' => $events,
			'from' => $from,
			'to' => $to
		));

		$model->setTemplate('news-digest');

		//QUEUE
		//	try to connect to Queue and send messages to it.
		//	this will try to send messages to mail_queue, that will
		//	send them on it's way via a MailTransport
		try{
			$connectionFactory = $sm->get('Stjornvisi\Lib\QueueConnectionFactory');
			$connection = $connectionFactory->createConnection();
			$channel = $connection->channel();

			$channel->queue_declare('mail_queue', false, true, false, false);

			foreach ($users as $user){
				$model->setVariable('user',$user);
				$msg = new AMQPMessage(
					json_encode( (object)array(
						'subject' => "Vikan framundan | {$from->format('j. n.')} - {$to->format('j. n. Y')}",
						'body' => $renderer->render($model),
						'recipient' => (object)array(
							'name' => $user->name,
							'address' => $user->email,
						),
						'key' => md5(time())
					)),
					array('delivery_mode' => 2) # make message persistent
				);

				$channel->basic_publish($msg, '', 'mail_queue');
				$logger->info("Queue Service says: Fetching users who want upcoming events, {$user->email} in queue ");
			}
			$channel->close();
			$connection->close();
		//QUEUE RUNTIME EXCEPTION
		//
		}catch (AMQPRuntimeException $e){
			$logger->warn( "Can't start Mail Queue: {$e->getMessage()}" );
			exit(1);
		//EXCEPTION
		//
		}catch (\Exception $e){
			$logger->warn( print_r($e->getTraceAsString(),true) );
			exit(1);
		}

		$logger->info("Queue Service says: Fetching upcoming events done, users are in queue");
	}

	/**
	 * Will create all images.
	 *
	 * <code>
	 * 	$ php path/to/index.php image-generate --ignore
	 * </code>
	 * @throws \RuntimeException
	 */
	public function imageGenerateAction(){

		$request = $this->getRequest();
		// Make sure that we are running in a console and the user has not tricked our
		// application into running this action from a public web server.
		if (!$request instanceof ConsoleRequest){
			throw new \RuntimeException('You can only use this action from a console!');
		}

		$path = './module/Stjornvisi/public/stjornvisi/images/';
		$ignore   = $this->getRequest()->getParam('ignore', false);
		$counter = 0;
		$index = 0;
		$report = (object)array('processed'=>0, 'ignored'=>0);



		//COUNT
		//	count how many file there are.
		foreach (new DirectoryIterator($path.'original') as $fileInfo) {
			if( $fileInfo->isDot() || !preg_match('/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $fileInfo->getFilename()) ) {
				continue;
			}else{
				$counter++;
			}
		}

		$adapter = new Console();
		$progressBar = new ProgressBar($adapter, $index, $counter);

		//FOR EVERY
		//	for every file in directory...
		foreach (new DirectoryIterator($path.'original') as $fileInfo) {
			//IF NOT IMAGE
			//	if not image - igonre and move on
			if( $fileInfo->isDot() || !preg_match('/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $fileInfo->getFilename()) ) {
				continue;
			}

			//IGNORE
			//	should the script ignore images that
			//	have already been converted.
			if( $ignore ){
				if(is_file( "{$path}60/{$fileInfo->getFilename()}" )){
					$progressBar->update($index++);
					++$report->ignored;
					continue;
				}
			}

			try{

				//60 SQUARE
				//	create an cropped image with hard height/width of 60
				$imagine = new Imagine();
				$image = $imagine->open($fileInfo->getPathname());
				$transform = new Transformation();
				$transform->add( new Square() );
				$transform->add( new Resize( new Box(60,60) ) );
				$transform->apply( $image )->save($path .'60/'. $fileInfo->getFilename());
				$imagine = null;

				//300 SQUARE
				//	create an cropped image with hard height/width of 300
				$imagine = new Imagine();
				$image = $imagine->open($fileInfo->getPathname());
				$transform = new Transformation();
				$transform->add( new Square() );
				$transform->add( new Resize( new Box(300,300) ) );
				$transform->apply( $image )->save($path .'300-square/'. $fileInfo->getFilename());
				$imagine = null;

				//300 NORMAL
				//	create an image that is not cropped and will
				//	have a width of 300
				$imagine = new Imagine();
				$image = $imagine->open($fileInfo->getPathname());
				$size = $image->getSize()->widen(300);
				$image->resize($size)
					->save($path .'300/'. $fileInfo->getFilename());
				$imagine = null;

				$imagine = new Imagine();
				$image = $imagine->open($fileInfo->getPathname());
				$transform = new Transformation();
				$transform->add( new Square() );
				$transform->add( new Resize( new Box(100,100) ) );
				$transform->apply( $image )->save($path .'100/'. $fileInfo->getFilename());
				$imagine = null;
			}catch (\Exception $e){
				echo $e->getMessage().PHP_EOL;
			}

			++$report->processed;
			$progressBar->update($index++);

		}
		$progressBar->finish();
		var_dump($report);
	}

	/**
	 * Will start a listeners that listens for CRUD actions
	 * in service layer.
	 * If something happens there, this action will send a request
	 * to the queue server (RabbitMQ)
	 *
	 * @throws \RuntimeException
	 * @todo actually index entries ;)
	 */
	public function indexEntryAction(){

		$request = $this->getRequest();
		// Make sure that we are running in a console and the user has not tricked our
		// application into running this action from a public web server.
		if (!$request instanceof ConsoleRequest){
			throw new \RuntimeException('You can only use this action from a console!');
		}

		$sm = $this->getServiceLocator();
		$logger = $sm->get('Logger'); /** @var \Zend\Log\Logger */
		try{
			$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			$channel->queue_declare('search-index', false, false, false, false);

			$logger->info("Index Listener started, Waiting for messages. To exit press CTRL+C");

			$callback = function($msg) use($logger) {

				$params = json_decode($msg->body);


				/*

				switch($params['name']){
					case Article::NAME:
						$indexer = new ArticleIndex();
						$indexer->unindex((object)array('id'=>$params['id']),$this->index);
						if( $params['data'] ){
							$indexer->index($params['data'],$this->index);
						}
						break;
					case Event::NAME:
						$indexer = new EventIndex();
						$indexer->unindex((object)array('id'=>$params['id']),$this->index);
						if( $params['data'] ){
							$indexer->index($params['data'],$this->index);
						}
						break;
					case Group::NAME:
						$indexer = new GroupIndex();
						$indexer->unindex((object)array('id'=>$params['id']),$this->index);
						if( $params['data'] ){
							$indexer->index($params['data'],$this->index);
						}
						break;
					case News::NAME:
						$indexer = new NewsIndex();
						$indexer->unindex((object)array('id'=>$params['id']),$this->index);
						if( $params['data'] ){
							$indexer->index($params['data'],$this->index);
						}
						break;

					//case Event::GALLERY_NAME:
					//	$queue = $sm->get('Stjornvisi\Queue\Facebook\Album');
					//	$queue->send(json_encode((object)array(
					//		'data' => $params['data']
					//	)));
					//	break;
					default:
						$indexer = new NullIndex();
						break;
				}
				*/


				$logger->info("Index Listener, Received {$msg->body}".print_r($params,true));
			};

			$channel->basic_consume('search-index', '', false, true, false, false, $callback);

			while(count($channel->callbacks)) {
				$channel->wait();
			}
		}catch (\Exception $e){
			$logger->warn( "Can't start IndexListener: {$e->getMessage()}" );
			exit(1);
		}

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
	public function notifyAction(){

		$request = $this->getRequest();
		// Make sure that we are running in a console and the user has not tricked our
		// application into running this action from a public web server.
		if (!$request instanceof ConsoleRequest){
			throw new \RuntimeException('You can only use this action from a console!');
		}

		$sm = $this->getServiceLocator();
		$logger = $sm->get('Logger'); /** @var \Zend\Log\Logger */

		try{
			$connectionFactory = $sm->get('Stjornvisi\Lib\QueueConnectionFactory');
			$connection = $connectionFactory->createConnection();
			$channel = $connection->channel();

			$channel->queue_declare('notify_queue', false, true, false, false);

			$logger->info("Notice Listener started, Waiting for messages. To exit press CTRL+C");

			//THE MAGIC
			//	here is where everything happens. the rest of the code
			//	is just connect and deconnect to RabbitMQ
			$callback = function($msg) use ($logger, $sm){
				$message = json_decode( $msg->body );
				$handler = new \Stjornvisi\Notify\Null();
				try{
					$handler = $sm->get($message->action);
					$logger->info("Notify message [{$message->action}] obtained");
				}catch (ServiceNotFoundException $e){
					$logger->error("Notify message [{$message->action}] not found");
				}

				$handler->setData( $message );
				$handler->send();

				$logger->info("Notify message [{$message->action}] processed");
				$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

			};// end of - MAGIC

			$channel->basic_qos(null, 1, null);
			$channel->basic_consume('notify_queue', '', false, false, false, false, $callback);

			while(count($channel->callbacks)) {
				$channel->wait();
			}

			$channel->close();
			$connection->close();
		}catch (\Exception $e){
			$logger->warn( "Can't start NotifyListener: {$e->getMessage()}" );
			$logger->warn( print_r($e->getTraceAsString(),true) );
			exit(1);
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
	public function mailAction(){

		$request = $this->getRequest();
		// Make sure that we are running in a console and the user has not tricked our
		// application into running this action from a public web server.
		if (!$request instanceof ConsoleRequest){
			throw new \RuntimeException('You can only use this action from a console!');
		}

		$sm = $this->getServiceLocator();
		$logger = $sm->get('Logger'); /** @var $logger \Zend\Log\Logger */

		try{
			$connectionFactory = $sm->get('Stjornvisi\Lib\QueueConnectionFactory');
			$connection = $connectionFactory->createConnection();
			$channel = $connection->channel();
			$channel->queue_declare('mail_queue', false, true, false, false);

			$logger->info("Mail Queue started, Waiting for messages. To exit press CTRL+C");

			$classname = get_class($this);

			//THE MAGIC
			//	here is where everything happens. the rest of the code
			//	is just connect and deconnect to RabbitMQ
			$callback = function($msg) use ($logger, $sm, $classname){

				//JSON VALID
				//	fist make sure that the JSON is valid
				if( ($messageObject = json_decode( $msg->body )) !== null ){

					//VALIDATE OBJECT
					//	make sure that the basic objects are there
					//TODO there has to be a better way of doing this
					if( isset($messageObject->recipient) &&
						isset($messageObject->recipient->address) &&
						isset($messageObject->recipient->name) &&
						isset($messageObject->subject) &&
						isset($messageObject->body)){
						$logger->info( "Send e-mail to [{$messageObject->recipient->address}, {$messageObject->recipient->name}]" );

						//CREATE / SEND
						//	create a mail message and actually send it
						$message = new Message();
						$message->addTo($messageObject->recipient->address,$messageObject->recipient->name)
							->addFrom('stjornvisi@stjornvisi.is')
							->setSubject($messageObject->subject)
							->setBody($messageObject->body);

						if($this->getRequest()->getParam('debug', false)){
							$logger->debug( "{$classname}:mailAction ". print_r($messageObject,true) );
						}else if($this->getRequest()->getParam('trace', false)){
							$logger->debug( "{$classname}:mailAction ". $message->toString() );
						}else{
							try{
								$transport = $sm->get('MailTransport');
								$transport->send($message);
							}catch (\Exception $e){
								$logger->err( $e->getMessage() . "\n" .$e->getTraceAsString() );
							}
						}

					}else{
						$logger->warn( "Mail Message object is missing values ". print_r($messageObject,true) );
					}

				//JSON INVALID
				//	the message could not ne decoded
				}else{
					$logger->warn("Could not decode mail-message");
				}

				$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

			};// end of - MAGIC

			$channel->basic_qos(null, 1, null);
			$channel->basic_consume('mail_queue', '', false, false, false, false, $callback);

			while(count($channel->callbacks)) {
				$channel->wait();
			}

			$channel->close();
			$connection->close();

		}catch (AMQPRuntimeException $e){
			$logger->warn( "Can't start Mail Queue: {$e->getMessage()}" );
			exit(1);
		}catch (\Exception $e){
			$logger->warn( "{$e->getMessage() }\n".print_r($e->getTraceAsString(),true) );
			exit(1);
		}

	}
}
