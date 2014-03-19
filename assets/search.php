<?php
	chdir(dirname(__DIR__.'/../../../../'));

	require 'init_autoloader.php';

	//BOOTSTRAP
	//	bootstrap the application.
	//	this has to be done to get all configs
	$sm = Zend\Mvc\Application::init(require 'config/application.config.php')
		->bootstrap()
		->getServiceManager();
	$pdo = $sm->get('PDO');
	$index = $sm->get('Search\Index\Search');


	//Event
	//	index event entries.
	//
	$counter = 0;
	$eventService = new Stjornvisi\Service\Event( $pdo );
	$events = $eventService->fetchAll();
	$adapter = new Zend\Progressbar\Adapter\Console();

	echo "\nIndexing Event entries\n";
	$progressBar = new Zend\ProgressBar\ProgressBar($adapter, $counter, count($events));
	$i = new Stjornvisi\Search\Index\Event();
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
	$newsService = new Stjornvisi\Service\News( $pdo );
	$news = $newsService->fetchAll();
	$adapter = new Zend\Progressbar\Adapter\Console();

	echo "\nIndexing News entries\n";
	$progressBar = new Zend\ProgressBar\ProgressBar($adapter, $counter, count($news));
	$i = new Stjornvisi\Search\Index\News();
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
	$articleService = new Stjornvisi\Service\Article( $pdo );
	$articles = $articleService->fetchAll();
	$adapter = new Zend\Progressbar\Adapter\Console();

	echo "\nIndexing News entries\n";
	$progressBar = new Zend\ProgressBar\ProgressBar($adapter, $counter, count($articles));
	$i = new Stjornvisi\Search\Index\Article();
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
	$groupService = new Stjornvisi\Service\Group( $pdo );
	$groups = $groupService->fetchAll();
	$adapter = new Zend\Progressbar\Adapter\Console();

	echo "\nIndexing Group entries\n";
	$progressBar = new Zend\ProgressBar\ProgressBar($adapter, $counter, count($groups));
	$i = new Stjornvisi\Search\Index\Group();
	foreach($groups as $item){
		$i->index($item,$index);
		$progressBar->update(++$counter);
	}
	$index->commit();
	$progressBar->finish();
