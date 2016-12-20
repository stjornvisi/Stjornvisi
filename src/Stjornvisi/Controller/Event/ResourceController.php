<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 18/07/15
 * Time: 11:12 AM
 */

namespace Stjornvisi\Controller\Event;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Stjornvisi\Form\Resource as ResourceForm;

class ResourceController extends AbstractActionController
{
    /**
     * Get list of even't resources.
     *
     * @return ViewModel
     */
    public function listAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $authService = $sm->get('AuthenticationService');

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get($this->params()->fromRoute('id', 0))) != false) {
            $groupIds = $this->extractGroupIds($event->groups);

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //
            if ($access->is_admin || $access->type >= 1) {
                return (new ViewModel([
                    'event' => $event,
                    'resources' => $eventService->getResources($event->id)
                ]))->setTemplate('stjornvisi/event/resource-list');

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

    /**
     * Insert new resource for event.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function createAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $authService = $sm->get('AuthenticationService');

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get($this->params()->fromRoute('id', 0), ($authService->hasIdentity())?$authService->getIdentity()->id:null)) != false) {
            $groupIds = $this->extractGroupIds($event->groups);

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //
            if ($access->is_admin || $access->type >= 1) {
                $form = new ResourceForm();
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/resource-create', ['id'=>$event->id]));
                if ($this->request->isPost()) {
                    $form->setData($this->request->getPost());
                    //FORM VALID
                    if ($form->isValid()) {
                        $eventService->addResource($event->id, $form->getData());
                        return $this->redirect()->toRoute('vidburdir/resource-list', ['id'=>$event->id]);
                        //FORM INVALID
                        //
                    } else {
                        $this->getResponse()->setStatusCode(400);
                        return (new ViewModel([
                            'access' => $access,
                            'event' => $event,
                            'form' => $form
                        ]))->setTemplate('stjornvisi/event/resource-create');
                    }
                } else {
                    return (new ViewModel([
                        'access' => $access,
                        'event' => $event,
                        'form' => $form
                    ]))->setTemplate('stjornvisi/event/resource-create');
                }

                //ACCESS DENIED
                //	403
            } else {
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
            }
            //RESOURCE NOT FOUND
            //
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * Update resource item.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function updateAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = $sm->get('AuthenticationService');

        //ITEM FOUND
        //
        if (($item = $eventService->getResourceItem($this->params()->fromRoute('id', 0)) ) != false) {
            $event = $eventService->get($item->event_id);

            $groupIds = $this->extractGroupIds($event->groups);

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //	access granted
            if ($access->is_admin || $access->type >= 1) {
                $form = new ResourceForm();
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/resource-update', ['id'=>$item->id]));

                //POST
                //	post request
                if ($this->request->isPost()) {
                    $form->setData($this->request->getPost());
                    if ($form->isValid()) {
                        $eventService->updateResource($item->id, $form->getData());
                        return $this->redirect()->toRoute('vidburdir/resource-list', ['id'=>$event->id]);
                    } else {
                        $this->getResponse()->setStatusCode(400);
                        return (new ViewModel([
                            'event' => $event,
                            'form' => $form
                        ]))->setTemplate('stjornvisi/event/resource-update');
                    }
                    //QUERY
                    //	get request
                } else {
                    $form->bind(new \ArrayObject($item));
                    return (new ViewModel([
                        'event' => $event,
                        'form' => $form
                    ]))->setTemplate('stjornvisi/event/resource-update');
                }
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

    /**
     * Delete one resource item.
     *
     * @return \Zend\Http\Response
     */
    public function deleteAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = $sm->get('AuthenticationService');

        //ITEM FOUND
        //
        if (($item = $eventService->getResourceItem($this->params()->fromRoute('id', 0)) ) != false) {
            $event = $eventService->get($item->event_id);

            $groupIds = $this->extractGroupIds($event->groups);

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //	access granted
            if ($access->is_admin || $access->type >= 1) {
                $eventService->deleteResource($item->id);
                return $this->redirect()->toRoute('vidburdir/resource-list', ['id'=>$event->id]);
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

    /**
     * @param \Traversable $groups
     * @return array
     */
    private function extractGroupIds($groups)
    {
        return array_map(
            function ($i) {
                return $i->id;
            },
            $groups
        );
    }
}
