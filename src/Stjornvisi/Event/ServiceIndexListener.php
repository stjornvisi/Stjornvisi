<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 3/31/14
 * Time: 9:48 PM
 */

namespace Stjornvisi\Event;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;

use ZendSearch\Lucene\SearchIndexInterface;

use Stjornvisi\Search\Index\Article as ArticleIndex;
use Stjornvisi\Search\Index\Event as EventIndex;
use Stjornvisi\Search\Index\Group as GroupIndex;
use Stjornvisi\Search\Index\News as NewsIndex;
use Stjornvisi\Search\Index\Null as NullIndex;

use Stjornvisi\Service\Article;
use Stjornvisi\Service\Event;
use Stjornvisi\Service\Group;
use Stjornvisi\Service\News;

class ServiceIndexListener extends AbstractListenerAggregate {

	private $index;
	public function __construct( SearchIndexInterface $index ){
		$this->index = $index;
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
		$this->listeners[] = $events->attach('index', array($this, 'index'));
	}

	public function index(EventInterface $event){
		return;
		$params = $event->getParams();

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
			/*
			case Event::GALLERY_NAME:
				$queue = $sm->get('Stjornvisi\Queue\Facebook\Album');
				$queue->send(json_encode((object)array(
					'data' => $params['data']
				)));
				break;
			*/
			default:
				$indexer = new NullIndex();
				break;
		}
	}
} 