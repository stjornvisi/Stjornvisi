<?php
namespace Stjornvisi\Controller\Event;

use \DateTime;
use Stjornvisi\View\Model\CsvModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Stjornvisi\Form\Email;
use Stjornvisi\Form\Event as EventForm;
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
     * Also accepts POST request for (semi) anonymous
     * event registrations.
     *
     * @return array|ViewModel
     */
    public function indexAction()
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
            return $this->notFoundAction();
        }

        $groupIds = $this->extractGroupIds($event->groups);
        $access = $userService->getTypeByGroup($identity, $groupIds);
        $registerMessage = false;

        //POST
        //	post request
        if ($this->request->isPost()) {
            $registerMessage = true;
            $name = $this->params()->fromPost('email', '');
            $email = $this->params()->fromPost('name', '');
            $eventService->registerUser($event->id, $name, 1, $email);
            $this->getEventManager()->trigger(
                'notify',
                $this,
                [
                    'action' => 'Stjornvisi\Notify\Attend',
                    'data' => (object)[
                    'event_id' => $event->id,
                    'type' => 1,
                    'recipients' => (object)[
                        'id' => null,
                        'name' => $name,
                        'email' => $email
                        ],
                    ],
                ]
            );
        }

        return new ViewModel([
            'event' => $event,
            'access' => $access,
            'related' => $eventService->getRelated($groupIds, $event->id),
            'aggregate' => $eventService->aggregateAttendance($event->id),
            'register_message' => $registerMessage,
        ]);
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

        //DATE RANGE
        //  calculate and create dates that this
        //  request will span.
        $date = $this->params()->fromRoute('date', date('Y-m'));
        $prev = new DateTime($date.'-01');
        $prev->sub(new \DateInterval('P1M'));
        $current = new DateTime($date.'-01');
        $next = new DateTime($date.'-01');
        $next->add(new \DateInterval('P1M'));

        $events = $eventService->getRange($current, $next);

        $firstDay = (int)date('N', strtotime("{$current->format('Y-m')}-01"));
        $offset = ($firstDay-1)*-1;
        $calendar = [];
        for ($i=0; $i<42; $i++) {
            $from = strtotime("{$current->format('Y-m')}-01 00:00:00") +(60*60*24*$offset);
            $date = date('Y-m-d', $from);
            $calendar[$date] = array_filter(
                $events,
                function ($i) use ($date) {
                    return ($i->event_date->format('Y-m-d') == $date);
                }
            );
            $offset++;
        }

        return new ViewModel([
            'events' => $events,
            'prev' => $prev,
            'current' => $current,
            'next' => $next,
            'calendar' => $calendar
        ]);
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
        $authService = $sm->get('AuthenticationService');
        $group_id = $this->params()->fromRoute('id', false);
        $form = new EventForm($groupService->fetchAll());

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
                $id = $eventService->create($form->getObject());
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
        $authService = $sm->get('AuthenticationService');

        $event = $eventService->get($this->params()->fromRoute('id', 0));

        if (!$event) {
            return $this->notFoundAction();
        }

        $groupIds = $this->extractGroupIds($event->groups);
        $access = $userService->getTypeByGroup(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
            $groupIds
        );

        //ACCESS GRANTED
        //  user has access
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
                    $eventService->update($event->id, $form->getObject());
                    return $this->redirect()->toRoute('vidburdir/index', ['id'=>$event->id]);

                //INVALID
                //  form data is invalid
                } else {
                    $this->getResponse()->setStatusCode(400);
                    $view = new ViewModel(['form' => $form]);
                    return $view;
                }
            //QUERY
            //  http get request
            } else {
                $form->bind($event);
                $view = new ViewModel(['form' => $form]);
                return $view;
            }

        //ACCESS DENIED
        } else {
            $this->getResponse()->setStatusCode(401);
            $model = new ViewModel();
            $model->setTemplate('error/401');
            return $model;
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
            //  user has access
            if ($access->is_admin || $access->type >= 1) {
                $csv = new Csv();
                $csv->setHeader(['Nafn','Titill','Netfang','Fyrirtæki','Dags.']);
                $csv->setName('maetingarlisti'.date('Y-m-d-H:i').'.csv');

                foreach ($event->attenders as $item) {
                    $csv->add([
                        'name' => $item->name,
                        'title' => $item->title,
                        'email' => $item->email,
                        'company_name' => $item->company_name,
                        'register_time' => $item->register_time->format('Y-m-d H:i'),
                    ]);
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
                    'notify',
                    $this,
                    [
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
            //  user has access
            if ($access->is_admin || $access->type >= 1) {
                $form = new Email();
                $form->setAttribute(
                    'action',
                    $this->url()->fromRoute(
                        'vidburdir/send-mail',
                        [
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
                            'notify',
                            $this,
                            [
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
        switch($type){
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
