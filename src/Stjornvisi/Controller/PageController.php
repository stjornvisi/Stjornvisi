<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 12/03/14
 * Time: 10:38
 */

namespace Stjornvisi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PageController extends AbstractActionController {

	public function indexAction(){
		$sm = $this->getServiceLocator();
		$pageService = $sm->get('Stjornvisi\Service\Page');

		//FOUND
		//	page found
		if( ( $page = $pageService->get($this->request->getUri()->getPath()) ) != false ){
			return new ViewModel(array(
				'page' => $page
			));
		//NOT FOUND
		//	404
		}else{
			var_dump('404');
		}

	}

	public function updateAction(){
		$sm = $this->getServiceLocator();
		$pageService = $sm->get('Stjornvisi\Service\Page');

		//PAGE FOUND
		//
		if( ( $page = $pageService->getObject($this->params()->fromRoute('id',0) ) ) != false ){
			if( $this->request->isPost() ){
				$pageService->update(
					$this->params()->fromRoute('id',0),
					$this->request->getPost()->toArray()
				);
				return false;
			}else{

			}
		//NOT FOUND
		//	404
		}else{
			var_dump('404 no stuff');
		}

	}
} 
