<?php

namespace Stjornvisi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;
use ZendSearch\Lucene;

class SearchController extends AbstractActionController
{
    public function searchAction()
    {
        $index = Lucene\Lucene::open('./data/search/');
        $searchResults = $index->find(
            'title:'.$this->params()->fromQuery('q')
        );
        return new ViewModel(array(
            'result' => $searchResults
        ));
    }

    public function autocompleteAction()
    {
        $index = Lucene\Lucene::open('./data/search/');
        $searchResults = $index->find(
            'title:'.$this->params()->fromRoute('term')
        );
        $result = array();
        foreach ($searchResults as $item) {
            if ($item->score >= 0.8) {
                $href = '';
                $type = '';
                switch($item->type){
                    case 'article':
                        $href = $this->url()->fromRoute('greinar/index', array('id'=>$item->identifier));
                        $type = "Grein";
                        break;
                    case 'event':
                        $href = $this->url()->fromRoute('vidburdir/index', array('id'=>$item->identifier));
                        $type = "Viðburður";
                        break;
                    case 'group':
                        $href = $this->url()->fromRoute('hopur/index', array('id'=>$item->identifier));
                        $type = "Hópur";
                        break;
                    case 'news':
                        $href = $this->url()->fromRoute('frettir/index', array('id'=>$item->identifier));
                        $type = "Frétt";
                        break;
                    default:
                        $href = "#";
                        $type = "?";
                        break;
                }
                $result[] = (object)array(
                    'value' => $item->title,
                    'type' => $type,
                    'href' => $href
                );
            }
        }
        return new JsonModel($result);
    }
}
