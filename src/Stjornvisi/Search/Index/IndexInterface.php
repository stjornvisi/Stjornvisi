<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 16/03/14
 * Time: 22:11
 */

namespace Stjornvisi\Search\Index;

use ZendSearch\Lucene\SearchIndexInterface;

interface IndexInterface
{
    /**
     * Index one entry.
     *
     * @param $data
     * @param SearchIndexInterface $index
     *
     * @return IndexInterface
     */
    public function index($data, SearchIndexInterface $index);

    /**
     * Un-index one entry.
     *
     * @param $data
     * @param SearchIndexInterface $index
     *
     * @return IndexInterface
     */
    public function unindex($data, SearchIndexInterface $index);
}
