<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 16/03/14
 * Time: 22:12
 */

namespace Stjornvisi\Search\Index;

use ZendSearch\Lucene\SearchIndexInterface;
use ZendSearch\Lucene\Document\Field;
use ZendSearch\Lucene\Document;

class Group implements IndexInterface {

	/**
	 * @param $data
	 * @param SearchIndexInterface $index
	 *
	 * @return IndexInterface
	 */
	public function index($data, SearchIndexInterface $index){

		$this->unindex($data, $index);

		$indexDoc = new Document();
		$indexDoc->addField(Field::Keyword('group_id',$data->id));
		$indexDoc->addField(Field::UnIndexed('type',"group"));
		$indexDoc->addField(Field::UnIndexed('identifier',$data->url));
		$indexDoc->addField(Field::UnIndexed('date_time',date('c') ));
		$indexDoc->addField(Field::UnIndexed('date',date('j. M. Y') ));

		$indexDoc->addField(Field::Text('title',$data->name_short,'utf-8'));
		$indexDoc->addField(Field::Text('body',$data->description,'utf-8'));

		$index->addDocument($indexDoc);

		return $this;
	}

	/**
	 * @param $data
	 * @param SearchIndexInterface $index
	 *
	 * @return IndexInterface
	 */
	public function unindex($data, SearchIndexInterface $index){
		$hits = $index->find('group_id:' . $data->id);
		foreach ($hits as $hit) {
			$index->delete($hit->id);
		}
		return $this;
	}
}
