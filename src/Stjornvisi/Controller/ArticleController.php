<?php
/**
 * Stjornvisi (http://stjornvisi.is)
 *
 * @link      https://github.com/fizk/Stjornvisi for the canonical source repository
 * @copyright Copyright (c) 2010-2014 IsProject. (http://isproject.is)
 * @license   http://opensource.org/licenses/MIT The MIT License (MIT)
 */

namespace Stjornvisi\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;
use Stjornvisi\Form\Article as ArticleForm;
use Stjornvisi\Form\Author as AuthorForm;

/**
 * Class ArticleController
 * @package Stjornvisi\Controller
 */
class ArticleController extends AbstractActionController{

    /**
     * Get one article.
     *
     * @return array|ViewModel
     */
    public function indexAction(){

        $sm = $this->getServiceLocator();
        $articleService = $sm->get('Stjornvisi\Service\Article');
		$userService = $sm->get('Stjornvisi\Service\User');

		$auth = new AuthenticationService();

        if ( ( $article = $articleService->get($this->params()->fromRoute('id',0)) ) != false ) {
            return new ViewModel(array(
                'article' => $article,
				'access' => $userService->getType(( $auth->hasIdentity() )
						? $auth->getIdentity()->id
						: null)
            ));
        } else {
			$this->getResponse()->setStatusCode(404);
			return;
        }
	}

    /**
     * Get list of articles.
     *
     * @return ViewModel
     */
    public function listAction()
	{

        $sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$articleService = $sm->get('Stjornvisi\Service\Article');

		$auth = new AuthenticationService();

        return new ViewModel(array(
            'articles' => $articleService->fetchAll(),
			'access' => $userService->getType(( $auth->hasIdentity() )
					? $auth->getIdentity()->id
					: null)
        ));
	}

	/**
	 * Create new article.
	 *
	 * @return HttpResponse|ViewModel
	 */
	public function createAction()
	{
		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$articleService = $sm->get('Stjornvisi\Service\Article');

		$auth = new AuthenticationService();
		$access = $userService->getType(( $auth->hasIdentity() )
			? $auth->getIdentity()->id
			: null);

		//ACCESS GRANTED
		//
		if( $access->is_admin ){

			$form = new ArticleForm( $articleService->fetchAllAuthors() );
			$form->setAttribute('action',$this->url()->fromRoute('greinar/create'));
			//POST
			//	post request
			if( $this->request->isPost() ){
				$form->setData( $this->request->getPost() );
				//VALID
				//	form is valid
				if( $form->isValid() ){
					$id = $articleService->create( $form->getData() );
					return $this->redirect()->toRoute('greinar/index',array('id'=> $id));
				//INVALID
				//	form is invalid
				}else{
					$this->getResponse()->setStatusCode(400);
					return new ViewModel(array(
						'form' => $form
					));

				}
			//QUERY
			//	get request
			}else{
				return new ViewModel(array(
					'form' => $form
				));
			}
		//ACCESS DENIED
		//
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}
	}

	/**
	 * Delete one article.
	 *
	 * @return HttpResponse
	 */
	public function deleteAction()
	{
		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$articleService = $sm->get('Stjornvisi\Service\Article');

		$auth = new AuthenticationService();
		$access = $userService->getType(( $auth->hasIdentity() )
			? $auth->getIdentity()->id
			: null);

		//ACCESS GRANTED
		//
		if( $access->is_admin ){

			//ARTICLE FOUND
			//	found
			if( ( $article = $articleService->get($this->params()->fromRoute('id',0)) ) != false ){
				$articleService->delete($article->id);
				return $this->redirect()->toRoute('greinar');
			//NOT FOUND
			//	404
			}else{
				var_dump('404');
			}
		//ACCESS DENIED
		//
		}else{
			var_dump('403');
		}
	}

	/**
	 * Update article.
	 *
	 * @return HttpResponse|ViewModel
	 */
	public function updateAction()
	{

		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$articleService = $sm->get('Stjornvisi\Service\Article');

		$auth = new AuthenticationService();
		$access = $userService->getType(( $auth->hasIdentity() )
			? $auth->getIdentity()->id
			: null);

		//ACCESS GRANTED
		//
		if( $access->is_admin ){

			$id = $this->params()->fromRoute('id',0);

			//ARTICLE FOUND
			//	article in database
			if( ( $article = $articleService->get($this->params()->fromRoute('id',0)) ) != false ){

				$form = new ArticleForm( $articleService->fetchAllAuthors() );
				$form->setAttribute('action',$this->url()->fromRoute('greinar/update',array('id'=>$id)));

				//POST
				//	post request
				if( $this->request->isPost() ){
					$form->setData($this->request->getPost());
					//VALID FORM
					//	form is valid
					if( $form->isValid() ){
						$articleService->update($id,$form->getData());
						return $this->redirect()->toRoute('greinar/index',array('id'=>$id));
					//INVALID FORM
					//	form is invalid
					}else{
						return new ViewModel(array(
							'form' => $form
						));
					}
				//QUERY
				//	get request
				}else{
					$form->bind( new \ArrayObject($article) );
					return new ViewModel(array(
						'form' => $form
					));
				}
			//NOT FOUND
			//	404
			}else{
				var_dump('404');
			}

		//ACCESS DENIED
		//
		}else{
			var_dump('404');
		}

	}

	/**
	 * List all authors.
	 *
	 * @return ViewModel
	 */
	public function listAuthorAction()
	{
		$sm = $this->getServiceLocator();
		$articleService = $sm->get('Stjornvisi\Service\Article');
		return new ViewModel(array(
			'authors' => $articleService->fetchAllAuthors()
		));
	}

	/**
	 * Create article author.
	 *
	 * @return HttpResponse|ViewModel
	 */
	public function createAuthorAction()
	{

		$sm = $this->getServiceLocator();
		$articleService = $sm->get('Stjornvisi\Service\Article');
		$auth = new AuthenticationService();
		//ACCESS GRANTED
		//
		if( $auth->hasIdentity() ){

			$form = new AuthorForm();
			$form->setAttribute('action',$this->url()->fromRoute('greinar/author-create'));

			//POST
			//
			if( $this->request->isPost() ){

				$form->setData( $this->request->getPost() );
				//VALID FORM
				//
				if($form->isValid()){
					$articleService->createAuthor(  $form->getData() );
					return $this->redirect()->toRoute('greinar/author-list');
				//INVALID FORM
				//
				}else{
					return new ViewModel(array(
						'form' => $form
					));
				}
			//QUERY
			//	get request
			}else{
				return new ViewModel(array(
					'form' => $form
				));
			}
		//ACCESS DENIED
		//	no access
		}else{
			var_dump('403');
		}
	}

	/**
	 * Update article author.
	 *
	 * @return HttpResponse|ViewModel
	 */
	public function updateAuthorAction()
	{

		$sm = $this->getServiceLocator();
		$articleService = $sm->get('Stjornvisi\Service\Article');

		//FIND AUTHOR
		//
		if( ($author = $articleService->getAuthor( $this->params()->fromRoute('id',0) )) != false ){
			$form = new AuthorForm();
			$form->setAttribute(
				'action',
				$this->url()->fromRoute('greinar/author-update',array('id'=>$author->id))
			);

			//POST
			//	post request
			if( $this->request->isPost() ){
				$form->setData($this->request->getPost());
				//VALID FORM
				//
				if($form->isValid()){
					$articleService->updateAuthor( $author->id, $form->getData() );
					return $this->redirect()->toRoute('greinar/author-list');
				//INVALID
				//
				}else{
					return new ViewModel(array(
						'form' => $form
					));
				}
			//QUERY
			//	get request
			}else{
				$form->bind( new \ArrayObject($author) );
				return new ViewModel(array(
					'form' => $form
				));
			}

		//AUTHOR NOT FOUND
		//	404
		}else{
			var_dump('404');
		}
	}

	/**
	 * Delete one article author.
	 *
	 * @return HttpResponse
	 */
	public function deleteAuthorAction()
	{
		$sm = $this->getServiceLocator();
		$articleService = $sm->get('Stjornvisi\Service\Article');
		$author = new AuthenticationService();
		//ACCESS GRANTED
		//
		if( $author->hasIdentity() ){
			//FIND AUTHOR
			//
			if( ($author = $articleService->getAuthor( $this->params()->fromRoute('id',0) )) != false ){

				$articleService->deleteAuthor($author->id);
				return $this->redirect()->toRoute('greinar/author-list');

				//AUTHOR NOT FOUND
				//	404
			}else{
				var_dump('404');
			}
		//ACCESS DENIED
		//
		}else{
			var_dump('403');
		}

	}
}
