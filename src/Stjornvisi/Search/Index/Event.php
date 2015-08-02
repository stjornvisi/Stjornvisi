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

class Event implements IndexInterface
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
        $indexDoc->addField(Field::Keyword('event_id', $data->id));
        $indexDoc->addField(Field::UnIndexed('type', "event"));
        $indexDoc->addField(Field::UnIndexed('identifier', $data->id));
        $indexDoc->addField(Field::UnIndexed('date_time', $data->event_date->format('c')));
        $indexDoc->addField(Field::UnIndexed('date', $data->event_date->format('j. M. Y')));

        $indexDoc->addField(Field::Text('title', $data->subject, 'utf-8'));
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
        $hits = $index->find('event_id:' . $data->id);
        foreach ($hits as $hit) {
            $index->delete($hit->id);
        }
        return $this;
    }
}
