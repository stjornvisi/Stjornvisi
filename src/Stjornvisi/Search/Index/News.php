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

class News implements IndexInterface
{
    /**
     * @param $data
     * @param SearchIndexInterface $index
     *
     * @return IndexInterface
     */
    public function index($data, SearchIndexInterface $index)
    {
        $this->unindex($data, $index);

        $indexDoc = new Document();
        $indexDoc->addField(Field::Keyword('news_id', $data->id));
        $indexDoc->addField(Field::UnIndexed('type', "news"));
        $indexDoc->addField(Field::UnIndexed('identifier', $data->id));
        $indexDoc->addField(Field::UnIndexed('date_time', $data->created_date->format('c')));
        $indexDoc->addField(Field::UnIndexed('date', $data->created_date->format('j. M. Y')));

        $indexDoc->addField(Field::Text('title', $data->title, 'utf-8'));
        $indexDoc->addField(Field::Text('body', $data->body, 'utf-8'));

        $index->addDocument($indexDoc);

        return $this;
    }

    /**
     * @param $data
     * @param SearchIndexInterface $index
     *
     * @return IndexInterface
     */
    public function unindex($data, SearchIndexInterface $index)
    {
        $hits = $index->find('news_id:' . $data->id);
        foreach ($hits as $hit) {
            $index->delete($hit->id);
        }
        return $this;
    }
}
