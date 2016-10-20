<?php

namespace Stjornvisi\Controller\Event;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Stjornvisi\Form\EventDatepicker as DateForm;

class DateController extends AbstractActionController
{
    public function listAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = $sm->get('AuthenticationService');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $access = $userService->getTypeByGroup(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null, null
        );

        //ACCESS GRANTED
        //
        if ($access->is_admin || $access->type >= 2) {
            return (new ViewModel([
                'access' => $access,
                'dates' => $eventService->getDatepickerDates()
            ]))->setTemplate('stjornvisi/event/date-list');

            //ACCESS DENIED
            //
        } else {
            $this->getResponse()->setStatusCode(401);
            $model = new ViewModel();
            $model->setTemplate('error/401');
            return $model;
        }
    }

    public function createAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = $sm->get('AuthenticationService');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $access = $userService->getTypeByGroup(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null, null
        );

        $id = $this->params()->fromRoute('id', 0);

        //ACCESS GRANTED
        //
        if ($access->is_admin || $access->type >= 2) {
            $form = new DateForm();

            if ($id) {
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/dates/update', ['id'=>$id]));
            }
            else {
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/dates/create'));
            }

            if ($this->request->isPost()) {
                $data = $this->request->getPost();
                $form->setData($data);

                if (!$data['annualdate'] && !$data['specificdate']) {
                    $form->get('annualdate')->setMessages(['Engin dagsetning hefur verið valin']);
                }

                //FORM VALID
                if ($form->isValid()) {
                    $eventService->addDatepickerDate($form->getData());
                    return $this->redirect()->toRoute('vidburdir/dates');
                    //FORM INVALID
                    //
                } else {
                    $this->getResponse()->setStatusCode(400);
                    return (new ViewModel([
                        'access' => $access,
                        'form' => $form
                    ]))->setTemplate('stjornvisi/event/date-create');
                }
            } else {
                return (new ViewModel([
                    'access' => $access,
                    'form' => $form
                ]))->setTemplate('stjornvisi/event/date-create');
            }

            //ACCESS DENIED
            //	403
        } else {
            $this->getResponse()->setStatusCode(401);
            $model = new ViewModel();
            $model->setTemplate('error/401');
            return $model;
        }
    }

    public function deleteAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = $sm->get('AuthenticationService');

        $access = $userService->getTypeByGroup(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null, null
        );

        $timestamp = $this->params()->fromRoute('timestamp', 0);

        //ITEM FOUND
        //
        if (($item = $eventService->getResourceItem($timestamp) ) != false) {

            //ACCESS GRANTED
            //	access granted
            if ($access->is_admin || $access->type >= 2) {
                $eventService->deleteDatepickerDate($timestamp);
                return $this->redirect()->toRoute('vidburdir/dates');
                //ACCESS DENIED
                //
            } else {
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
            }

            //NOT FOUND
            //	404
        } else {
            return $this->notFoundAction();
        }
    }
}
