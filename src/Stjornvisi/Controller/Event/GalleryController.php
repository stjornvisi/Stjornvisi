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
use Stjornvisi\Form\Gallery as GalleryForm;

class GalleryController extends AbstractActionController
{
    /**
     * Get list of even't images.
     *
     * @return ViewModel
     */
    public function galleryListAction()
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
            if ($access->is_admin || $access->type >= 2) {
                return (new ViewModel([
                    'event' => $event,
                    'gallery' => $eventService->getGallery($event->id)
                ]))->setTemplate('stjornvisi/event/gallery-list');

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
     * Insert new image into gallery for event.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function galleryCreateAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $authService = $sm->get('AuthenticationService');
        $identity = ($authService->hasIdentity())
            ? $authService->getIdentity()->id
            : null;
        $event = $eventService->get($this->params()->fromRoute('id', 0), $identity);

        if (!$event) {
            $this->notFoundAction();
        }

        $groupIds = $this->extractGroupIds($event->groups);
        $access = $userService->getTypeByGroup($identity, $groupIds);

        //ACCESS GRANTED
        //
        if ($access->is_admin || $access->type >= 2) {
            $form = new GalleryForm();
            $form->setAttribute(
                'action',
                $this->url()->fromRoute('vidburdir/gallery-create', ['id'=>$event->id])
            );

            //POST
            //	post request
            if ($this->request->isPost()) {
                $form->setData($this->request->getPost());
                //FORM VALID
                if ($form->isValid()) {
                    $eventService->addGallery($event->id, $form->getData());
                    return $this->redirect()->toRoute('vidburdir/gallery-list', ['id'=>$event->id]);
                    //FORM INVALID
                    //
                } else {
                    $this->getResponse()->setStatusCode(400);
                }
            }
            return (new ViewModel([
                'access' => $access,
                'event' => $event,
                'form' => $form
            ]))->setTemplate('stjornvisi/event/gallery-create');

            //ACCESS DENIED
            //	403
        } else {
            $this->getResponse()->setStatusCode(401);
            $model = new ViewModel();
            $model->setTemplate('error/401');
            return $model;
        }

    }

    /**
     * Update gallery item.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function galleryUpdateAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = $sm->get('AuthenticationService');

        //ITEM FOUND
        //
        if (($item = $eventService->getGalleryItem($this->params()->fromRoute('id', 0)) ) != false) {
            $event = $eventService->get($item->event_id);

            $groupIds = $this->extractGroupIds($event->groups);

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //	access granted
            if ($access->is_admin || $access->type >= 2) {
                $form = new GalleryForm();
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/gallery-update', ['id'=>$item->id]));

                //POST
                //	post request
                if ($this->request->isPost()) {
                    $form->setData($this->request->getPost());
                    if ($form->isValid()) {
                        $eventService->updateGallery($item->id, $form->getData());
                        return $this->redirect()->toRoute('vidburdir/gallery-list', ['id'=>$event->id]);
                    } else {
                        $this->getResponse()->setStatusCode(400);
                        return new ViewModel(['event' => $event, 'form' => $form]);
                    }
                    //QUERY
                    //	get request
                } else {
                    $form->bind(new \ArrayObject($item));
                    return (new ViewModel(['event' => $event, 'form' => $form]))
                        ->setTemplate('stjornvisi/event/gallery-update');
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
     * Delete one gallery item.
     *
     * @return \Zend\Http\Response
     */
    public function galleryDeleteAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = $sm->get('AuthenticationService');

        //ITEM FOUND
        //
        if (($item = $eventService->getGalleryItem($this->params()->fromRoute('id', 0)) ) != false) {
            $event = $eventService->get($item->event_id);

            $groupIds = $this->extractGroupIds($event->groups);

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //	access granted
            if ($access->is_admin || $access->type >= 2) {
                $eventService->deleteGallery($item->id);
                return $this->redirect()->toRoute('vidburdir/gallery-list', ['id'=>$event->id]);
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
