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
            return new ViewModel(array(
                'news' => $news,
                'related' => $newsService->getRelated($news->group_id,$news->id),
                'access' => $userService->getTypeByGroup(
                        ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                        $news->group_id
                    )
            ));
        }else{
            return $this->notFoundAction();
        }

	}
	
	/**
	 * Display a list of news entries
	 */
	public function listAction(){
		$newsDAO = new Application_Model_NewsEntry();
		$this->view->news = Zend_Paginator::factory($newsDAO->select()->order('created_date DESC'));
		$this->view->news->setItemCountPerPage(30);
		$this->view->news->setCurrentPageNumber($this->_getParam('page'));
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
                $form->setAttribute('action',"/frettir/stofna/{$group->id}");

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
                    return new ViewModel(array(
                        'form' => $form
                    ));
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
                $form->setAttribute('action',"/frettir/{$news->id}/uppfaera");

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
                    return new ViewModel(array(
                        'news' => $news,
                        'form' => $form ,
                    ));
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