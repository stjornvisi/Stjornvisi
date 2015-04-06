<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 12/03/14
 * Time: 10:38
 */

namespace Stjornvisi\Controller;

use Stjornvisi\Form\Page;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class PageController.
 *
 * @package Stjornvisi\Controller
 */
class PageController extends AbstractActionController
{
    /**
     * Display one static page.
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $sm = $this->getServiceLocator();
        $pageService = $sm->get('Stjornvisi\Service\Page');
        $userService = $sm->get('Stjornvisi\Service\User');

        $authService = new AuthenticationService();
        $access = $userService->getTypeByGroup(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
            null
        );

        //FOUND
        //	page found
        if (( $page = $pageService->get($this->request->getUri()->getPath()) ) != false) {
            return new ViewModel(['page' => $page, 'admin' => $access->is_admin]);
            //NOT FOUND
            //	404
        } else {
            return $this->notFoundAction();
        }

    }

    /**
     * Update one static page.
     *
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function updateAction()
    {
        $sm = $this->getServiceLocator();
        $pageService = $sm->get('Stjornvisi\Service\Page');

        //PAGE FOUND
        //
        if (($page = $pageService->getObject($this->params()->fromRoute('id', 0)) ) != false) {
            $form = new Page();
            if ($this->request->isPost()) {
                $form->setData($this->request->getPost());
                if ($form->isValid() ) {

                    $data = $form->getData();
                    unset($data['submit']);
                    $pageService->update($page->id, $data);
                    return $this->redirect()->toUrl($page->label);
                } else {
                    return new ViewModel(['form' => $form]);
                }
            } else {
                $form->bind(new \ArrayObject((array)$page));
                return new ViewModel(['form' => $form]);
            }
            //NOT FOUND
            //	404
        } else {
            return $this->notFoundAction();
        }
    }
} 
