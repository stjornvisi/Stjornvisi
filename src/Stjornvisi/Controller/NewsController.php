<?php
namespace Stjornvisi\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayObject;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Stjornvisi\Form\News as NewsForm;

/**
 * Class NewsController.
 *
 * @package Stjornvisi\Controller
 */
class NewsController extends AbstractActionController{

	/**
	 * Entries per page
	 */
	const NEWS_COUNT_PER_PAGE = 15;

	/**
	 * Display one news entry.
	 *
	 * @return array|ViewModel
	 */
	public function indexAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $newsService = $sm->get('Stjornvisi\Service\News');

        $authService = new AuthenticationService();

        if( ( $news = $newsService->get($this->params()->fromRoute('id')) ) != false ){
			$access = $userService->getTypeByGroup(
				($authService->hasIdentity())?$authService->getIdentity()->id:null,
				$news->group_id
			);
			$mainView = new ViewModel();
			$entryView = new ViewModel(array(
				'news' => $news,
				'access' => $access,
			));
			$entryView->setTemplate('stjornvisi/news/partials/index-news');
			$asideView = new ViewModel(array(
				'news' => $news,
				'related' => $newsService->getRelated($news->group_id,$news->id),
				'access' => $access,
			));
			$asideView->setTemplate('stjornvisi/news/partials/index-aside');

			$mainView->addChild($entryView,'news')
				->addChild($asideView,'aside');

			return ($this->request->isXmlHttpRequest())
				?  $entryView->setTerminal(true)
				: $mainView ;

        }else{
            return $this->notFoundAction();
        }

	}

	/**
	 * Display a list of news entries.
	 *
	 * @return ViewModel
	 */
	public function listAction(){
		$sm = $this->getServiceLocator();
		$newsService = $sm->get('Stjornvisi\Service\News');
		$page = ($this->params()->fromRoute('no',0) == 0)
			? 0
			: $this->params()->fromRoute('no',0) -1;
		$news = $newsService->fetchAll(
			$page*NewsController::NEWS_COUNT_PER_PAGE,
			NewsController::NEWS_COUNT_PER_PAGE
		);
		$count = $newsService->count();
		return new ViewModel(array(
			'news' => $news,
			'count' => $newsService->count(),
			'pages' => (int)$count/NewsController::NEWS_COUNT_PER_PAGE,
			'no' => $page
		));
	}

	/**
	 * Create new news entry.
	 *
	 * @return array|\Zend\Http\Response|ViewModel
	 */
	public function createAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $newsService = $sm->get('Stjornvisi\Service\News');
        $groupService = $sm->get('Stjornvisi\Service\Group');

        $authService = new AuthenticationService();

		//ACCESS
		//	let's check access
		$access = $userService->getTypeByGroup(
			($authService->hasIdentity())?$authService->getIdentity()->id:null,
			$this->params()->fromRoute('id',null)
		);


		//NO GROUP-ID AND NO ADMIN
		//	the group-id param is not set and the user
		//	is not admin.... lets just stop right here
		if( $this->params()->fromRoute('id',null) == null && !$access->is_admin ){
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}

		//NO GROUP
		//	so let's mock one
		if( $this->params()->fromRoute('id',null) == null ){
			$group = (object)array(
				'id' => null
			);
		//GROUP
		//	let's get the group
		}else{
			$group = $groupService->get($this->params()->fromRoute('id'));
			//GROUP NOT FOUND
			//	if the group was not found: then 404
			if( $group == false ){
				return $this->notFoundAction();
			}
		}

		//ACCESS GRANTED
		//  access granted
		if($access->is_admin || $access->type >= 1){

			$form = new NewsForm();
			$form->setAttribute('action', $this->url()->fromRoute('frettir/create',array('id'=>$group->id)) );

			//POST
			//  http post request
			if( $this->request->isPost() ){

				$form->setData( $this->request->getPost() );
				//VALID
				//  form is valid
				if( $form->isValid() ){
					$data = $form->getData();
					unset($data['submit']);
					$data['user_id'] = $authService->getIdentity()->id;
					$data['group_id'] = $group->id;
					$newsId = $newsService->create($data);
					return $this->redirect()->toRoute('frettir/index',array('id'=>$newsId));
					//INVALID
					//  form data is invalid
				}else{
					$this->getResponse()->setStatusCode(400);
					return new ViewModel(array(
						'form' => $form,
						'group' => $group
					));
				}
				//QUERY
				//  http get request
			}else{
				return new ViewModel(array(
					'form' => $form,
					'group' => $group
				));
			}

		//ACCESS DENIED
		//  access denied
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}

	}

	/**
	 * Update one news entry.
	 *
	 * @return array|\Zend\Http\Response|ViewModel
	 */
	public function updateAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $newsService = $sm->get('Stjornvisi\Service\News');
		$groupService = $sm->get('Stjornvisi\Service\Group');

        $authService = new AuthenticationService();

        if( ( $news = $newsService->get($this->params()->fromRoute('id')) ) != false ){


            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                $news->group_id
            );

            //ACCESS GRANTED
            //  access in granted
            if( $access->is_admin || $access->type >= 1 ){
                $form = new NewsForm();
                $form->setAttribute('action',$this->url()->fromRoute('frettir/update',array('id'=>$news->id)));

                //POST
                //  post request
                if( $this->request->isPost() ){
                    $form->setData( $this->request->getPost() );

                    //VALID FORM
                    //  form data is valid
                    if($form->isValid()){
                        $data = $form->getData();
                        unset($data['submit']);
                        $newsService->update($news->id,$data);
                        return $this->redirect()->toRoute('frettir/index',array('id'=>$news->id));
                    //INVALID
                    //  form data is invalid
                    }else{
						$this->getResponse()->setStatusCode(400);
                        return new ViewModel(array(
                            'news' => $news,
                            'form' => $form ,
							'group' => $groupService->get($news->group_id),
                        ));
                    }
                //QUERY
                //  get request
                }else{
                    $form->bind( new ArrayObject((array)$news));
					$view = new ViewModel(array(
						'news' => $news,
						'form' => $form ,
						'group' => $groupService->get($news->group_id),
					));

					$view->setTerminal( $this->request->isXmlHttpRequest() );
                    return $view;
                }
            //ACCESS DENIED
            //  no access
            }else{
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
            }



        }else{
			return $this->notFoundAction();
        }
	}

	/**
	 * Delete one news entry.
	 *
	 * @return array|\Zend\Http\Response|ViewModel
	 */
	public function deleteAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $newsService = $sm->get('Stjornvisi\Service\News');

        $authService = new AuthenticationService();

        if( ( $news = $newsService->get($this->params()->fromRoute('id')) ) != false ){

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                $news->group_id
            );

            //ACCESS GRANTED
            //  access in granted
            if( $access->is_admin || $access->type >= 1 ){
                $newsService->delete($news->id);
				return $this->redirect()->toRoute('frettir');
            //ACCESS DENIED
            //  access denied
            }else{
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
            }
        }else{
			return $this->notFoundAction();
        }
	}

}
