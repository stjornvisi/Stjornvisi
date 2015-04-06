<?php
/**
 * Created by PhpStorm.
 * User: hilmar
 * Date: 17/01/15
 * Time: 19:35
 */

namespace Stjornvisi\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;
use Stjornvisi\Form\Conference as ConferenceForm;

class ConferenceController extends AbstractActionController
{
    public function listAction()
    {
        $sm = $this->getServiceLocator();
        $conferenceService = $sm->get('Stjornvisi\Service\Conference');
		/** @var $conferenceService \Stjornvisi\Service\Conference */
        return new ViewModel(['conferences' => $conferenceService->fetchAll() ]);
    }

    public function indexAction()
    {
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $conferenceService = $sm->get('Stjornvisi\Service\Conference');
		/** @var $conferenceService \Stjornvisi\Service\Conference */
        $authService = new AuthenticationService();
        //CONFERENCE FOUND
        //  an conference with this ID was found
        if (($conference = $conferenceService->get(
				$this->params()->fromRoute('id', 0),
				($authService->hasIdentity())
					? $authService->getIdentity()->id
					: null)) != false )
		{
            $groupIds = array_map(
                function ($i) {
                    return $i->id;
                }, $conference->groups
            );
            //TODO don't use $_POST
            //TODO send registration mail
            if ($this->request->isPost()) {
                $conferenceService->registerUser(
                    $conference->id,
                    $this->params()->fromPost('email', ''),
                    1,
                    $this->params()->fromPost('name', '')
                );
                $this->getEventManager()->trigger(
                    'notify', $this, array(
                    'action' => 'Stjornvisi\Notify\Attend',
                    'data' => (object)array(
                                'conference_id' => $conference->id,
                                'type' => 1,
                                'recipients' => (object)array(
                                        'id' => null,
                                        'name' => $this->params()->fromPost('name', ''),
                                        'email' => $this->params()->fromPost('email', '')
                                    ),
                    ),
                    )
                );
                return new ViewModel(
                    array(
                    'logged_in' => $authService->hasIdentity(),
                    'register_message' => true,
                    'conference' => $conference,
                    'related' => $conferenceService->getRelated($groupIds),
                    'attendees' => $userService->getByConference($conference->id),
                    'access' => $userService->getTypeByGroup(
                        ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                        $groupIds
                    ),
                    )
                );
            } else {
                $conferenceView = new ViewModel(
                    array(
                    'conference' => $conference,
                    'register_message' => false,
                    'logged_in' => $authService->hasIdentity(),
                    'access' => $userService->getTypeByGroup(
                        ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                        $groupIds
                    ),
                    'attendees' => $userService->getByConference($conference->id),
                    )
                );
                $conferenceView->setTemplate('stjornvisi/conference/partials/index-conference');
                $asideView = new ViewModel(
                    array(
                    'access' => $userService->getTypeByGroup(
                        ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                        $groupIds
                    ),
                    'conference' => $conference,
                    'related' => null//$conferenceService->getRelated($groupIds,$conference->id),
                    )
                );
                $asideView->setTemplate('stjornvisi/conference/partials/index-aside');
                $mainView = new ViewModel();
                $mainView
                    ->addChild($conferenceView, 'conference')
                    ->addChild($asideView, 'aside');
                return $mainView;
            }
            //NOT FOUND
            //  todo 404
        } else {
            var_dump('404');
        }
        /**
        $sm = $this->getServiceLocator();
        $conferenceService = $sm->get('Stjornvisi\Service\Conference'); /** @var $conferenceService \Stjornvisi\Service\Conference
        $userService = $sm->get('Stjornvisi\Service\User');
        $auth = new AuthenticationService();
        if ( ( $conference = $conferenceService->get($this->params()->fromRoute('id',0)) ) != false ) {
        return new ViewModel(array(
        'conference' => $conference,
        'access' => $userService->getType(( $auth->hasIdentity() )
        ? $auth->getIdentity()->id
        : null)
        ));
        } else {
        $this->getResponse()->setStatusCode(404);
        return;
        }
*/
    }
    /**
     * Create one conference.
     *
     * @return string|\Zend\Http\Response|ViewModel
     */
    public function createAction()
    {
        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $userService = $sm->get('Stjornvisi\Service\User');
        $conferenceService = $sm->get('Stjornvisi\Service\Conference');
        $group_id = $this->params()->fromRoute('id', false);
        $form = new ConferenceForm($groupService->fetchAll());
        $authService = new AuthenticationService();
        $access = $userService->getTypeByGroup(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
            $group_id
        );
        //GLOBAL EVENT
        //  this is a global event, only admin has access
        if($group_id === false ) {
            //ACCESS DENIED
            if(!$access->is_admin) {
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
                //ACCESS GRANTED
                //
            }else{
                $form->setAttribute('action', $this->url()->fromRoute('radstefna/create'));
            }
            //GROUPS EVENT
            //  this is a group event accessible to admin and group
            //  managers
        }else{
            //ACCESS GRANTED
            //  user is admin or manager
            if($access->is_admin || $access->type >= 1) {
                $form->setAttribute('action', $this->url()->fromRoute('radstefna/create', array('id'=>$group_id)));
                $form->bind(new \ArrayObject(array('groups'=>array($group_id) )));
                //ACCESS DENIED
                //  user is not a manager or admin
            }else{
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
            }
        }
        //POST
        if($this->request->isPost() ) {
            $form->setData($this->request->getPost());
            if($form->isValid() ) {
                $data = (array)$form->getData();
                unset($data['submit']);
                $mapService = $sm->get('Stjornvisi\Service\Map');
                /**
 * @var  $mapService \Stjornvisi\Service\JaMap 
*/
                $mapResult = $mapService->request(isset($data['address']) ? $data['address']: null);
                $data['lat'] = $mapResult->lat;
                $data['lng'] = $mapResult->lng;
                $id = $conferenceService->create($data);
                return $this->redirect()->toRoute('radstefna/index', array('id'=>$id));
            }else{
                $this->getResponse()->setStatusCode(400);
                return new ViewModel(
                    array(
                    'form' => $form
                    )
                );
            }
            //QUERY
        }else{
            return new ViewModel(
                array(
                'form' => $form
                )
            );
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
        $conferenceService = $sm->get('Stjornvisi\Service\Conference');
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $mapService = $sm->get('Stjornvisi\Service\Map');
        $authService = new AuthenticationService();
        //EVENT FOUND
        //  an event with this ID was found
        if(($conference = $conferenceService->get($this->params()->fromRoute('id', 0))) != false ) {
            $groupIds = array_map(
                function ($i) {
                    return $i->id; 
                }, $conference->groups
            );
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );
            //ACCESS GRANTED
            //  user has accss
            if($access->is_admin || $access->type >= 1 ) {
                $form = new ConferenceForm($groupService->fetchAll());
                $form->setAttribute('action', $this->url()->fromRoute('radstefna/update', array('id'=>$conference->id)));
                //POST
                //  http post request
                if($this->request->isPost() ) {
                    $form->setData($this->request->getPost());
                    //VALID
                    //  form data is valid
                    if($form->isValid() ) {
                        $data = $form->getData();
                        unset($data['submit']);
                        $mapService = $sm->get('Stjornvisi\Service\Map');
                        /**
 * @var  $maService \Stjornvisi\Service\JaMap 
*/
                        $mapResult = $mapService->request(isset($data['address']) ? $data['address']: null);
                        $data['lat'] = $mapResult->lat;
                        $data['lng'] = $mapResult->lng;
                        $conferenceService->update($conference->id, $data);
                        if($this->request->isXmlHttpRequest() ) {
                            $view = new ViewModel(
                                array(
                                'conference' => $conferenceService->get($conference->id),
                                'register_message' => false,
                                'logged_in' => $authService->hasIdentity(),
                                'access' => $userService->getTypeByGroup(
                                    ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                                    $groupIds
                                ),
                                'attendees' => $userService->getByEvent($conference->id),
                                )
                            );
                            $view->setTemplate('stjornvisi/conference/partials/index-event');
                            $view->setTerminal(true);
                            return $view;
                        }else{
                            return $this->redirect()->toRoute('radstefna/index', array('id'=>$conference->id));
                        }
                        //INVALID
                        //  form data is invalid
                    }else{
                        $this->getResponse()->setStatusCode(400);
                        $view = new ViewModel(
                            array(
                            'form' => $form,
                            )
                        );
                        $view->setTerminal($this->request->isXmlHttpRequest());
                        return $view;
                    }
                    //QUERY
                    //  http get request
                }else{
                    $form->bind(new \ArrayObject((array)$conference));
                    $view = new ViewModel(
                        array(
                        'form' => $form
                        )
                    );
                    $view->setTerminal($this->request->isXmlHttpRequest());
                    return $view;
                }
                //ACCESS DENIED
            }else{
                $this->getResponse()->setStatusCode(401);
                $model = new ViewModel();
                $model->setTemplate('error/401');
                return $model;
            }
            //NOT FOUND
            //	entry not found
        }else{
            $this->getResponse()->setStatusCode(404);
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
        $conferenceService = $sm->get('Stjornvisi\Service\Conference');
        $authService = new AuthenticationService();
        //EVENT FOUND
        //  an event with this ID was found
        if(($conference = $conferenceService->get($this->params()->fromRoute('id', 0))) != false ) {
            $groupIds = array_map(
                function ($i) {
                    return $i->id; 
                }, $conference->groups
            );
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );
            //ACCESS GRANTED
            //  user can delete
            if($access->is_admin || $access->type >= 1) {
                $conferenceService->delete($conference->id);
                return $this->redirect()->toRoute('radstefna');
                //ACCESS DENIED
                //  user can't delete
            }else{
                var_dump('403');
            }
            //EVENT NOT FOUND
            //
        }else{
            var_dump('404');
        }
    }
    /**
     * Set if user is going to attend a conference or not
     * This action is listening for the parameter <em>type</em>
     * that maps 1 to yes and 0 to no.
     *
     * @return \Zend\Http\Response
     */
    public function attendAction()
    {
        $sm = $this->getServiceLocator();
        $conferenceService = $sm->get('Stjornvisi\Service\Conference');
        $authService = new AuthenticationService();
        //EVENT FOUND
        //  event found in storage
        if(($conference = $conferenceService->get($this->params()->fromRoute('id', 0))) ) {
            //ACCESS
            //
            if($authService->hasIdentity()) {
                $conferenceService->registerUser(
                    $conference->id,
                    $authService->getIdentity()->id,
                    $this->params()->fromRoute('type', 0)
                );
                $this->getEventManager()->trigger(
                    'notify', $this, array(
                    'action' => 'Stjornvisi\Notify\Attend',
                    'data' => (object)array(
                            'recipients' => (int)$authService->getIdentity()->id,
                            'conference_id' => $conference->id,
                            'type' => $this->params()->fromRoute('type', 0)
                    ),
                    )
                );
                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
                //ACCESS DENIED
                //
            }else{
                var_dump('403');
            }
            //EVENT NOT FOUND
            //  todo 404
        }else{
            var_dump('404');
        }
    }
} 