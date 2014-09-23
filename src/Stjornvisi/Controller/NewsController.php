<?php
namespace Stjornvisi\Controller;

/**
 * Handles groups
 *
 * @category Stjornvisi
 * @package Controller
 * @author einarvalur
 *
 */

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\ArrayObject;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Stjornvisi\Form\News as NewsForm;

class NewsController extends AbstractActionController{

	/**
	 * Display one news entry
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
	 * Display a list of news entries
	 */
	public function listAction(){
		$perPage = 30;
		$sm = $this->getServiceLocator();
		$newsService = $sm->get('Stjornvisi\Service\News');
		$news = $newsService->fetchAll(
			$this->params()->fromRoute('no',0)*$perPage,
			$perPage
		);
		$count = $newsService->count();
		return new ViewModel(array(
			'news' => $news,
			'count' => $newsService->count(),
			'pages' => (int)$count/30,
			'no' => $this->params()->fromRoute('no',0)
		));
	}

	/**
	 * Create new news entry
	 */
	public function createAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $newsService = $sm->get('Stjornvisi\Service\News');
        $groupService = $sm->get('Stjornvisi\Service\Group');

        $authService = new AuthenticationService();



        if( ( $group = $groupService->get($this->params()->fromRoute('id')) ) != false ){

            $access = $userService->getTypeByGroup(
			    ($authService->hasIdentity())?$authService->getIdentity()->id:null,
			    $group->id
			);

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
                        return new ViewModel(array(
                            'form' => $form
                        ));
                    }
                //QUERY
                //  http get request
                }else{
					$view = new ViewModel(array(
						'form' => $form
					));

					return $view;
                }


            //ACCESS DENIED
            //  access denied
            }else{
                var_dump('403');
            }

        }else{
            var_dump('404');
        }
	}

	/**
	 * Update one news entry
     *
	 */
	public function updateAction(){
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
                        return new ViewModel(array(
                            'news' => $news,
                            'form' => $form ,
                        ));
                    }
                //QUERY
                //  get request
                }else{
                    $form->bind( new ArrayObject((array)$news));
					$view = new ViewModel(array(
						'news' => $news,
						'form' => $form ,
					));

					$view->setTerminal( $this->request->isXmlHttpRequest() );
                    return $view;
                }
            //ACCESS DENIED
            //  no access
            }else{
                var_dump('403');
            }



        }else{
            var_dump('404');
        }
	}

	/**
	 * Delete one news entry.
     *
	 * @todo Redirect to a better place
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
            //ACCESS DENIED
            //  access denied
            }else{
                var_dump('403');
            }
        }else{
            var_dump('404');
        }

		
	}

}
