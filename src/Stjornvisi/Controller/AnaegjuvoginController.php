<?php

namespace Stjornvisi\Controller;

use Stjornvisi\Form\Anaegjuvogin;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;

/**
 * Class AnaegjuvoginController.
 *
 * Handle all request to Anaegjuvog.
 *
 * @package Stjornvisi\Controller
 */
class AnaegjuvoginController extends AbstractActionController
{
    /**
     * Get one Anaegjuvog.
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $auth = new AuthenticationService();
        $sm = $this->getServiceLocator();
        $anaegjuvogin = $sm->get('Stjornvisi\Service\Anaegjuvogin');
        /** @var $anaegjuvogin \Stjornvisi\Service\Anaegjuvogin */

        if (($entry = $anaegjuvogin->getYear($this->params('year', null))) != false) {
            return new ViewModel([
                'years' => $anaegjuvogin->fetchYears(),
                'entry' => $entry,
                'identity' => $auth->hasIdentity()
            ]);
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * Get list of Anaegjuvog.
     *
     * @return ViewModel
     */
    public function listAction()
    {
        $auth = new AuthenticationService();
        $sm = $this->getServiceLocator();
        $anaegjuvogin = $sm->get('Stjornvisi\Service\Anaegjuvogin');
        /** @var $anaegjuvogin \Stjornvisi\Service\Anaegjuvogin */

        if (($entry = $anaegjuvogin->getIndex()) != false) {
            $view = new ViewModel([
                'years' => $anaegjuvogin->fetchYears(),
                'entry' => $entry,
                'identity' => $auth->hasIdentity()
            ]);

            return $view;
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * Create new Anaegjuvog.
     *
     * @return HttpResponse|ViewModel
     */
    public function createAction()
    {
        $sm = $this->getServiceLocator();
        $anaegjuvogin = $sm->get('Stjornvisi\Service\Anaegjuvogin');
        /** @var $anaegjuvogin \Stjornvisi\Service\Anaegjuvogin */

        $form = new Anaegjuvogin();
        $form->setAttribute('action', $this->url()->fromRoute('anaegjuvogin/create'));

        //POST
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());

            //VALID
            //  valid form
            if ($form->isValid()) {
                $anaegjuvogin->create($form->getData());
                if (!empty($form->get('year')->getValue())) {
                    return $this->redirect()
                        ->toRoute('anaegjuvogin/index', ['year'=>$form->get('year')->getValue()]);
                } else {
                    return $this->redirect()->toRoute('anaegjuvogin');
                }

            //INVALID
            //  invalid form
            } else {
                return new ViewModel(['form' => $form]);
            }

        //QUERY
        //
        } else {
            return new ViewModel(['form' => $form]);
        }
    }

    /**
     * Delete one Anaegjuvog.
     *
     * @return HttpResponse
     */
    public function deleteAction()
    {

    }

    /**
     * Update Anaegjuvog.
     *
     * @return HttpResponse|ViewModel
     */
    public function updateAction()
    {
        $sm = $this->getServiceLocator();
        $anaegjuvogin = $sm->get('Stjornvisi\Service\Anaegjuvogin');
        /** @var $anaegjuvogin \Stjornvisi\Service\Anaegjuvogin */

        //ENTRY FOUND
        //  entry was found
        if (($entry = $anaegjuvogin->get($this->params('id'))) != false) {
            $form = new Anaegjuvogin();
            $form->setAttribute('action', $this->url()->fromRoute('anaegjuvogin/update', ['id'=>$entry->id]));

            //POST
            //  post request
            if ($this->getRequest()->isPost()) {
                $form->setData($this->getRequest()->getPost());

                //VALID
                //  form is valid
                if ($form->isValid()) {
                    $anaegjuvogin->update($entry->id, $form->getData());
                    if ($entry->year) {
                        return $this->redirect()->toRoute('anaegjuvogin/index', ['year'=>$entry->year]);
                    } else {
                        return $this->redirect()->toRoute('anaegjuvogin');
                    }

                //INVALID
                //  form is invalid
                } else {
                    return new ViewModel(['form' => $form]);
                }

            //QUERY
            //  get request
            } else {
                $form->bind(new \ArrayObject($entry));
                return new ViewModel(['form' => $form]);
            }

        //ENTRY NOT FOUND
        //  did not find the entry
        } else {
            return $this->notFoundAction();
        }
    }
}
