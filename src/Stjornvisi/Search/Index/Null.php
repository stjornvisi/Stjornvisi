<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 26/03/14
 * Time: 13:41
 */

namespace Stjornvisi\Search\Index;

use ZendSearch\Lucene\SearchIndexInterface;

class Null implements IndexInterface
{
    /**
     * Index one entry.
     *
     * @param $data
     * @param SearchIndexInterface $index
     *
     * @return IndexInterface
     */
    public function index($data, SearchIndexInterface $index)
    {
        return $this;
    }

    /**
     * Un-index one entry.
     *
     * @param $data
     * @param SearchIndexInterface $index
     *
     * @return IndexInterface
     */
    public function unindex($data, SearchIndexInterface $index)
    {
        return $this;
    }
}
