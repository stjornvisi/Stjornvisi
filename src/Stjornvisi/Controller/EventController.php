<?php
namespace Stjornvisi\Controller;

use \DateTime;
use Stjornvisi\View\Model\CsvModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Stjornvisi\Form\Email;
use Stjornvisi\Form\Event as EventForm;
use Stjornvisi\Form\Gallery as GalleryForm;
use Stjornvisi\Form\Resource as ResourceForm;
use Stjornvisi\Lib\Csv;

/**
 * Class EventController.
 *
 * @package Stjornvisi\Controller
 */
class EventController extends AbstractActionController
{
    /**
     * Display one event.
     *
     * @return array|ViewModel
     */
    public function indexAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get(
            $this->params()->fromRoute('id', 0),
            ($authService->hasIdentity())?$authService->getIdentity()->id:null 
        )) != false ) {

            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $event->groups
            );

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                $groupIds
            );

            //POST
            //	post request
            if ($this->request->isPost()) {
                $eventService->registerUser(
                    $event->id,
                    $this->params()->fromPost('email', ''),
                    1,
                    $this->params()->fromPost('name', '')
                );
                $this->getEventManager()->trigger(
                    'notify', $this, [
                    'action' => 'Stjornvisi\Notify\Attend',
                    'data' => (object)[
                    'event_id' => $event->id,
                    'type' => 1,
                    'recipients' => (object)[
                    'id' => null,
                    'name' => $this->params()->fromPost('name', ''),
                    'email' => $this->params()->fromPost('email', '')
                    ],
                    ],
                    ]
                );

                $eventView = new ViewModel(
                    [
                    'event' => $event,
                    'register_message' => true,
                    'logged_in' => $authService->hasIdentity(),
                    'access' => $access,
                    ]
                );
                $eventView->setTemplate('stjornvisi/event/partials/index-event');
                $asideView = new ViewModel(
                    [
                    'access' => $access,
                    'event' => $event,
                    'related' => $eventService->getRelated($groupIds, $event->id),
                    ]
                );
                $asideView->setTemplate('stjornvisi/event/partials/index-aside');

                $mainView = new ViewModel(
                    [
                    'access' => $access,
                    'attendees' => $userService->getByEvent($event->id),
                    'aggregate' => $eventService->aggregateAttendance($event->id)
                    ]
                );
                $mainView
                    ->addChild($eventView, 'event')
                    ->addChild($asideView, 'aside');
                return $mainView;

			//QUERY
			//	get request
            } else {

                $eventView = new ViewModel(
                    [
                    'event' => $event,
                    'register_message' => false,
                    'logged_in' => $authService->hasIdentity(),
                    'access' => $access,
                    ]
                );
                $eventView->setTemplate('stjornvisi/event/partials/index-event');
                $asideView = new ViewModel(
                    [
                    'access' => $access,
                    'event' => $event,
                    'related' => $eventService->getRelated($groupIds, $event->id),
                    ]
                );
                $asideView->setTemplate('stjornvisi/event/partials/index-aside');

                $mainView = new ViewModel(
                    [
                    'logged_in' => $authService->hasIdentity(),
                    'access' => $access,
                    'attendees' => $userService->getByEvent($event->id),
                    'aggregate' => $eventService->aggregateAttendance($event->id)
                    ]
                );
                $mainView
                    ->addChild($eventView, 'event')
                    ->addChild($asideView, 'aside');
                return $mainView;
            }


		//NOT FOUND
		//  resource not found
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * List events in a given period
     * ...both in a table and in a list.
     *
     * @return ViewModel
     */
    public function listAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $date = $this->params()->fromRoute('date', date('Y-m'));
        $prev = new DateTime($date.'-01');
        $prev->sub(new \DateInterval('P1M'));
        $current = new DateTime($date.'-01');
        $next = new DateTime($date.'-01');
        $next->add(new \DateInterval('P1M'));

        $events = $eventService->getRange($current, $next);

        $firstDay = (int)date('N', strtotime("{$current->format('Y-m')}-01"));
        $offset = ($firstDay-1)*-1;
        $empty = true;
        $array = [];
        for ($i=0;$i<42;$i++) {
            $from = strtotime("{$current->format('Y-m')}-01 00:00:00") +(60*60*24*$offset);
            $to = strtotime("{$current->format('Y-m')}-01 23:59:59") +(60*60*24*$offset);
            $date = date('Y-m-d', $from);
            $array[$date] = array_filter(
                $events, function ($i) use ($date ) {
                    if ($i->event_date->format('Y-m-d') == $date) {
                        return true;
                    } else {
                        return false;
                    }
                }
            );
            $offset++;
        }

        return new ViewModel(
            [
            'events' => $events,
            'prev' => $prev,
            'current' => $current,
            'next' => $next,
            'calendar' => $array
            ]
        );

    }

    /**
     * Create one event.
     *
     * @return string|\Zend\Http\Response|ViewModel
     */
    public function createAction()
    {
        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $group_id = $this->params()->fromRoute('id', false);
        $form = new EventForm($groupService->fetchAll());

        $authService = new AuthenticationService();
        $access = $userService->getTypeByGroup(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
            $group_id
        );

        //GLOBAL EVENT
        //  this is a global event, only admin has access
        if ($group_id === false) {
            //ACCESS DENIED
            //	access denied
            if (!$access->is_admin) {
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
                //ACCESS GRANTED
                //	access granted
            } else {
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/create'));
            }
		//GROUPS EVENT
		//  this is a group event accessible to admin and group
		//  managers
        } else {
            //ACCESS GRANTED
            //  user is admin or manager
            if ($access->is_admin || $access->type >= 1) {
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/create', ['id'=>$group_id]));
                $form->bind(new \ArrayObject(['groups'=>[$group_id]]));
                //ACCESS DENIED
                //  user is not a manager or admin
            } else {
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
            }
        }

        //POST
        if ($this->request->isPost()) {
            $form->setData($this->request->getPost());
            if ($form->isValid()) {
                $data = (array)$form->getData();
                unset($data['submit']);

                /** Mapping Service removed, and fields exposed for editing */
                //$mapService = $sm->get('Stjornvisi\Service\Map');
                /** @var  $maService \Stjornvisi\Service\JaMap */
                //$mapResult = $mapService->request( isset($data['address']) ? $data['address']: null );
                //$data['lat'] = $mapResult->lat;
                //$data['lng'] = $mapResult->lng;

                   $id = $eventService->create($data);
                   return $this->redirect()->toRoute('vidburdir/index', ['id'=>$id]);
            } else {
                $this->getResponse()->setStatusCode(400);
                return new ViewModel(['form' => $form]);
            }
		//QUERY
        } else {
            return new ViewModel(['form' => $form]);
        }
    }

    /**
     * Update one event.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function updateAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $groupService = $sm->get('Stjornvisi\Service\Group');
        //$mapService = $sm->get('Stjornvisi\Service\Map');


        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get($this->params()->fromRoute('id', 0))) != false) {

            $groupIds = array_map(
                function ($i) {
                    return $i->id; 
                }, $event->groups
            );
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );
            //ACCESS GRANTED
            //  user has accss
            if ($access->is_admin || $access->type >= 1) {

                $form = new EventForm($groupService->fetchAll());
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/update', ['id'=>$event->id]));

                //POST
                //  http post request
                if ($this->request->isPost()) {
                    $form->setData($this->request->getPost());

                    //VALID
                    //  form data is valid
                    if ($form->isValid()) {

                        $data = $form->getData();
                        unset($data['submit']);
                        //$mapService = $sm->get('Stjornvisi\Service\Map');
                        /** @var  $maService \Stjornvisi\Service\JaMap */
                        //$mapResult = $mapService->request( isset($data['address']) ? $data['address']: null );
                        //$data['lat'] = $mapResult->lat;
                        //$data['lng'] = $mapResult->lng;

                        $eventService->update($event->id, $data);
                        if ($this->request->isXmlHttpRequest()) {
                            $view = new ViewModel(
                                [
                                'event' => $eventService->get($event->id),
                                'register_message' => false,
                                'logged_in' => $authService->hasIdentity(),
                                'access' => $userService->getTypeByGroup(
                                    ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                                    $groupIds
                                ),
                                'attendees' => $userService->getByEvent($event->id),
                                ]
                            );
                            $view->setTemplate('stjornvisi/event/partials/index-event');
                            $view->setTerminal(true);
                            return $view;
                        } else {
                            return $this->redirect()->toRoute('vidburdir/index', ['id'=>$event->id]);
                        }

					//INVALID
					//  form data is invalid
                    } else {
                        $this->getResponse()->setStatusCode(400);
                        $view = new ViewModel(['form' => $form]);
                        $view->setTerminal($this->request->isXmlHttpRequest());
                        return $view;
                    }
				//QUERY
				//  http get request
                } else {
                    $form->bind(new \ArrayObject((array)$event));
                    $view = new ViewModel(['form' => $form]);
                    $view->setTerminal($this->request->isXmlHttpRequest());
                    return $view;
                }

			//ACCESS DENIED
            } else {
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
            }

		//NOT FOUND
		//	entry not found
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * Delete one event.
     *
     * @return \Zend\Http\Response
     */
    public function deleteAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get($this->params()->fromRoute('id', 0))) != false) {
            $groupIds = array_map(
                function ($i) {
                    return $i->id; 
                }, $event->groups
            );
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //  user can delete
            if ($access->is_admin || $access->type >= 1) {
                $eventService->delete($event->id);
                return $this->redirect()->toRoute('vidburdir');
			//ACCESS DENIED
			//  user can't delete
            } else {
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
            }
		//EVENT NOT FOUND
		//
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * Export attendees list as csv.
     *
     * @return array|CsvModel|ViewModel
     */
    public function exportAttendeesAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get($this->params()->fromRoute('id', 0))) != false) {

            $groupIds = array_map(
                function ($i) {
                    return $i->id; 
                }, $event->groups
            );
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );
            //ACCESS GRANTED
            //  user has access
            if ($access->is_admin || $access->type >= 1) {

                $csv = new Csv();
                $csv->setHeader(['Nafn','Titill','Netfang','Dags.']);
                $csv->setName('maertingarlisti'.date('Y-m-d-H:i').'.csv');
                $resultset = $userService->getByEvent($event->id);
                foreach ($resultset as $item) {
                    $csv->add(
                        [
                        'name' => $item->name,
                        'title' => $item->title,
                        'email' => $item->email,
                        'register_time' => $item->register_time->format('Y-m-d H:i'),
                        ]
                    );
                }

                $model = new CsvModel();
                $model->setData($csv);

                return $model;
            } else {
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
            }
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * Set if user is going to attend an event or not
     * This action is listening for the parameter <em>type</em>
     * that maps 1 to yes and 0 to no.
     *
     * @return \Zend\Http\Response
     */
    public function attendAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  event found in storage
        if (($event = $eventService->get($this->params()->fromRoute('id', 0)))) {

            //ACCESS
            //
            if ($authService->hasIdentity()) {
                $eventService->registerUser(
                    $event->id,
                    $authService->getIdentity()->id,
                    $this->params()->fromRoute('type', 0)
                );
                $this->getEventManager()->trigger(
                    'notify', $this, [
                    'action' => 'Stjornvisi\Notify\Attend',
                    'data' => (object)[
                    'recipients' => (int)$authService->getIdentity()->id,
                    'event_id' => $event->id,
                    'type' => $this->params()->fromRoute('type', 0)
                    ],
                    ]
                );

                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
			//ACCESS DENIED
			//
            } else {
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
            }

		//EVENT NOT FOUND
		//  resource not found
        } else {
            return $this->notFoundAction();
        }
    }

    /**
     * Send mail to members of group(s) of events.
     *
     * @return ViewModel
     */
    public function sendMailAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get($this->params()->fromRoute('id', 0))) != false) {

            $groupIds = array_map(
                function ($i) {
                    return $i->id; 
                }, $event->groups
            );
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );
            //ACCESS GRANTED
            //  user has access
            if ($access->is_admin || $access->type >= 1) {
                $form = new Email();
                $form->setAttribute(
                    'action', $this->url()->fromRoute(
                        'vidburdir/send-mail', [
                        'id' => $event->id,
                        'type' => $this->params()->fromRoute('type', 'allir')
                        ]
                    )
                );
                //POST
                //	post request
                if ($this->request->isPost()) {
                    $form->setData($this->request->getPost());
                    //VALID
                    //	valid form
                    if ($form->isValid()) {
                        $this->getEventManager()->trigger(
                            'notify', $this, [
                            'action' => 'Stjornvisi\Notify\Event',
                            'data' => (object)[
                            'event_id' => $event->id,
                            'recipients' => ( $this->params()->fromRoute('type', 'allir') ),
                            'test' => (bool)$this->params()->fromPost('test', false),
                            'subject' => $form->get('subject')->getValue(),
                            'body' => $form->get('body')->getValue(),
                            'user_id' => $authService->getIdentity()->id
                            ],
                            ]
                        );

                        return new ViewModel(
                            [
                            'event' => $event,
                            'form' => $form,
                            'msg' => $this->params()->fromPost('test', false)
                                    ? 'Prufupóstur sendur'
                                    : 'Póstur sendur',
                            ]
                        );

					//INVALID
					//	invalid form
                    } else {
                        return new ViewModel(['event' => $event, 'form' => $form, 'msg' => false]);
                    }

				//QUERY
				//	get request
                } else {
                    return new ViewModel(['event' => $event, 'form' => $form, 'msg' => false]);
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
    }

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

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get($this->params()->fromRoute('id', 0))) != false) {

            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $event->groups
            );

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //
            if ($access->is_admin || $access->type >= 2) {

                return new ViewModel(
                    [
                    'event' => $event,
                    'gallery' => $eventService->getGallery($event->id)
                    ]
                );

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

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get(
            $this->params()->fromRoute('id', 0),
            ($authService->hasIdentity())?$authService->getIdentity()->id:null 
        )        ) != false 
        ) {

            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $event->groups
            );

            $authService = new AuthenticationService();
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

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
                        return new ViewModel(
                            [
                            'access' => $access,
                            'event' => $event,
                            'form' => $form
                            ]
                        );
                    }
				//QUERY
				//	get request
                } else {
                    return new ViewModel(
                        [
                        'access' => $access,
                        'event' => $event,
                        'form' => $form
                        ]
                    );
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
     * Update gallery item.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function galleryUpdateAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = new AuthenticationService();

        //ITEM FOUND
        //
        if (($item = $eventService->getGalleryItem($this->params()->fromRoute('id', 0)) ) != false) {

            $event = $eventService->get($item->event_id);

            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $event->groups
            );

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
                    return new ViewModel(['event' => $event, 'form' => $form]);
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
        $authService = new AuthenticationService();

        //ITEM FOUND
        //
        if (($item = $eventService->getGalleryItem($this->params()->fromRoute('id', 0)) ) != false) {

            $event = $eventService->get($item->event_id);

            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $event->groups
            );

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

    /**
     * Get list of even't resources.
     *
     * @return ViewModel
     */
    public function resourceListAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get($this->params()->fromRoute('id', 0))) != false) {

            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $event->groups
            );

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //
            if ($access->is_admin || $access->type >= 2) {

                return new ViewModel(
                    [
                    'event' => $event,
                    'resources' => $eventService->getResources($event->id)
                    ]
                );

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
    public function resourceCreateAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if (($event = $eventService->get(
            $this->params()->fromRoute('id', 0),
            ($authService->hasIdentity())?$authService->getIdentity()->id:null 
        )        ) != false 
        ) {

            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $event->groups
            );

            $authService = new AuthenticationService();
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //
            if ($access->is_admin || $access->type >= 2) {
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
                        return new ViewModel(
                            [
                            'access' => $access,
                            'event' => $event,
                            'form' => $form
                            ]
                        );
                    }
                } else {
                    return new ViewModel(
                        [
                        'access' => $access,
                        'event' => $event,
                        'form' => $form
                        ]
                    );
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
    public function resourceUpdateAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = new AuthenticationService();

        //ITEM FOUND
        //
        if (($item = $eventService->getResourceItem($this->params()->fromRoute('id', 0)) ) != false) {

            $event = $eventService->get($item->event_id);

            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $event->groups
            );

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //	access granted
            if ($access->is_admin || $access->type >= 2) {
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
                        return new ViewModel(
                            [
                            'event' => $event,
                            'form' => $form
                            ]
                        );
                    }
                    //QUERY
                    //	get request
                } else {
                    $form->bind(new \ArrayObject($item));
                    return new ViewModel(
                        [
                        'event' => $event,
                        'form' => $form
                        ]
                    );
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
    public function resourceDeleteAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $userService = $sm->get('Stjornvisi\Service\User');
        $authService = new AuthenticationService();

        //ITEM FOUND
        //
        if (($item = $eventService->getResourceItem($this->params()->fromRoute('id', 0)) ) != false) {

            $event = $eventService->get($item->event_id);

            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $event->groups
            );

            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //	access granted
            if ($access->is_admin || $access->type >= 2) {
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
     * Action for chart generation.
     * @return JsonModel
     * @todo not fully implemented
     */
    public function registryDistributionAction()
    {
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $type = $this->params()->fromRoute('type');
        $from = ($this->params()->fromRoute('from'))
        ? new DateTime($this->params()->fromRoute('from'))
        : null ;
        $to = ($this->params()->fromRoute('to'))
        ? new DateTime($this->params()->fromRoute('to'))
        : null ;
        $result = [];
        switch( $type ){
        case 'klukka':
            $result = $eventService->getRegistrationByHour($from, $to);
            break;
        case 'dagur':
            $result = $eventService->getRegistrationByDayOfWeek($from, $to);
            break;
        case 'manudur':
            $result = $eventService->getRegistrationByDayOfMonth($from, $to);
            break;
        default:
            $result = [];
            break;
        }
        return new JsonModel($result);
    }

    /**
     * Action for chart generation.
     * @return JsonModel
     * @todo not fully implemented
     */
    public function statisticsAction()
    {

    }
}
