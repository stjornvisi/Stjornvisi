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
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use \DateTime;
use \DateInterval;

class ConsoleController extends AbstractActionController {

	/**
	 * Rebuild search index.
	 *
	 * <code>
	 * $ php index.php search index
	 * </code>
	 *
	 * @throws \RuntimeException
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

	public function queueAction(){}

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

		$from = new DateTime();
		$from->add(new DateInterval('P1D'));
		$to = new DateTime();
		$to->add(new DateInterval('P8D'));

		$sm = $this->getServiceLocator();
		$eventService = $sm->get('Stjornvisi\Service\Event');
		$userService = $sm->get('Stjornvisi\Service\User');
		$events = $eventService->getRange($from, $to);


		$users = $userService->getUserMessage();

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


		$counter = 0;
		$adapter = new Console();
		$progressBar = new ProgressBar($adapter, $counter, count($users));

		$queue = $sm->get('Stjornvisi\Queue\Mail');
		foreach ($users as $user){
			$model->setVariable('user',$user);
			$queue->send(json_encode( (object)array(
				'subject' => "Vikan framundan | {$from->format('j. n.')} - {$to->format('j. n. Y')}",
				'body' => $renderer->render($model),
				'recipient' => $user->email,
				'user' => $user->name,
				'key' => md5(time())
			)));
			$progressBar->update(++$counter);
		}
		$progressBar->finish();
	}

	/**
	 * Upload images to facebook.
	 *
	 * <code>
	 * $ index.php facebook album
	 * </code>
	 *
	 * @throws \RuntimeException
	 */
	public function facebookAlbumUploadAction(){
		$request = $this->getRequest();
		// Make sure that we are running in a console and the user has not tricked our
		// application into running this action from a public web server.
		if (!$request instanceof ConsoleRequest){
			throw new \RuntimeException('You can only use this action from a console!');
		}

		$sm = $this->getServiceLocator();
		$queue = $sm->get('Stjornvisi\Queue\Facebook\Album');
		$messages = $queue->receive(5);
		foreach ($messages as $i => $message) {
			echo $message->body, "\n";

			// We have processed the message; now we remove it from the queue.
			$queue->deleteMessage($message);
		}
	}
} 
