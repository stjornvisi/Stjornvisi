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

use \DateTime;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Stjornvisi\Form\Email;
use Stjornvisi\Form\Event as EventForm;
use Stjornvisi\Form\Gallery as GalleryForm;
use Stjornvisi\Form\Resource as ResourceForm;
use Stjornvisi\Lib\Csv;


class EventController extends AbstractActionController{


	/**
	 * Display one event.
	 *
	 * @return array|ViewModel
	 */
	public function indexAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if( ($event = $eventService->get( $this->params()->fromRoute('id', 0), ($authService->hasIdentity())?$authService->getIdentity()->id:null )) != false ){

            $groupIds = array_map(function($i){
                return $i->id;
            },$event->groups);

            //TODO don't use $_POST
            //TODO send registration mail
            if( $this->request->isPost() ){
                $eventService->registerUser(
					$event->id,
					$this->params()->fromPost('email',''),
					1,
					$this->params()->fromPost('name','')
				);
				$this->getEventManager()->trigger('notify',$this,array(
					'action' => 'event.attend',
					'recipients' => (object)array(
							'id' => null,
							'name' => $this->params()->fromPost('name',''),
							'email' => $this->params()->fromPost('email','')
						),
					'priority' => true,
					'data' => (object)array(
							'event' => $event,
						),
				));

                return new ViewModel(array(
                    'logged_in' => $authService->hasIdentity(),
                    'register_message' => true,
                    'event' => $event,
                    'related' => $eventService->getRelated($groupIds),
                    'attendees' => $userService->getByEvent($event->id),
                    'access' => $userService->getTypeByGroup(
                            ($authService->hasIdentity())?$authService->getIdentity()->id:null,
                            $groupIds
                        ),
                ));
            }else{

				$eventView = new ViewModel(array(
					'event' => $event,
					'register_message' => false,
					'logged_in' => $authService->hasIdentity(),
					'access' => $userService->getTypeByGroup(
							($authService->hasIdentity())?$authService->getIdentity()->id:null,
							$groupIds
						),
					'attendees' => $userService->getByEvent($event->id),
				));
				$eventView->setTemplate('stjornvisi/event/partials/index-event');
				$asideView = new ViewModel(array(
					'access' => $userService->getTypeByGroup(
							($authService->hasIdentity())?$authService->getIdentity()->id:null,
							$groupIds
						),
					'event' => $event,
					'related' => $eventService->getRelated($groupIds,$event->id),
				));
				$asideView->setTemplate('stjornvisi/event/partials/index-aside');

				$mainView = new ViewModel();
				$mainView
					->addChild($eventView,'event')
					->addChild($asideView,'aside');
				return $mainView;
            }


        //NOT FOUND
        //  todo 404
        }else{
            var_dump('404');
        }
	}

	/**
	 * @todo implement
	 */
	public function listAction(){

		$sm = $this->getServiceLocator();
		$eventService = $sm->get('Stjornvisi\Service\Event');

		$date = $this->params()->fromRoute('date',date('Y-m'));
		$prev = new DateTime( $date.'-01' );
		$prev->sub(new \DateInterval('P1M'));
		$current = new DateTime( $date.'-01' );
		$next = new DateTime( $date.'-01' );
		$next->add( new \DateInterval('P1M'));

		$events = $eventService->getRange($current,$next);

		$firstDay = (int)date('N', strtotime("{$current->format('Y-m')}-01") );
		$offset = ($firstDay-1)*-1;
		$empty = true;
		$array = array();
		for($i=0;$i<42;$i++){
			$from = strtotime("{$current->format('Y-m')}-01 00:00:00") +(60*60*24*$offset);
			$to = strtotime("{$current->format('Y-m')}-01 23:59:59") +(60*60*24*$offset);
			$date = date('Y-m-d',$from);
			$array[$date] = array_filter($events,function($i) use ($date ){
				if( $i->event_date->format('Y-m-d') == $date ){
					return true;
				}else{
					return false;
				}
			});
			//$array[$date] = array();
			$offset++;
		}

		return new ViewModel(array(
			'events' => $events,
			'prev' => $prev,
			'current' => $current,
			'next' => $next,
			'calendar' => $array
		));

	}

	/**
	 * Create one event.
	 *
	 * @return string|\Zend\Http\Response|ViewModel
	 */
	public function createAction(){
        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $group_id = $this->params()->fromRoute('id', false);
        $form = new EventForm( $groupService->fetchAll() );

        $authService = new AuthenticationService();
        $access = $userService->getTypeByGroup(
            ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
            $group_id
        );

        //GLOBAL EVENT
        //  this is a global event, only admin has access
        if( $group_id === false ){
            //ACCESS DENIED
            if(!$access->is_admin){
                return '403';
            //ACCESS GRANTED
            //
            }else{
                $form->setAttribute('action',$this->url()->fromRoute('vidburdir/create'));
            }
        //GROUPS EVENT
        //  this is a group event accessible to admin and group
        //  managers
        }else{
            //ACCESS GRANTED
            //  user is admin or manager
            if($access->is_admin || $access->type >= 1){
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/create',array('id'=>$group_id)) );
                $form->bind( new \ArrayObject( array('groups'=>array($group_id) )));
            //ACCESS DENIED
            //  user is not a manager or admin
            }else{
                return '403';
            }
        }


        //POST
        if($this->request->isPost() ){
            $form->setData($this->request->getPost());
            if( $form->isValid() ){
                $data = (array)$form->getData();
                unset($data['submit']);
                $id = $eventService->create( $data );
                return $this->redirect()->toRoute('vidburdir/index',array('id'=>$id));
            }else{
                return new ViewModel(array(
                    'form' => $form
                ));
            }
        //QUERY
        }else{
            return new ViewModel(array(
                'form' => $form
            ));
        }


	/*
		//POST
		//	post request
		if( $this->_request->isPost() ){
			
			//ACCESS
			//	access granted
			if( $this->_helper->acl->validate( new Ext_Acl_Group($this->_getParam('groups')), Ext_Acl_Group::RULE_RESOURCE_CREATE ) ){
				$form = new Application_Form_Event();
				//VALID
				//	form is valid
				if($form->isValid($this->_request->getPost())){
					
					
						$_lat = null;
						$_lng = null;
						$_formatted_address = null;
						
						
						if( $form->getValue('location') ){
							try{
								$client = new Ext_Service_MapClient();
								$result = $client->request($form->getValue('location'));
								$_lat = $result->geometry->location->lat;
								$_lng = $result->geometry->location->lng;
								$_formatted_address = $result->formatted_address;
							}catch (Exception $e){
								if ($log = $this->_getLog()){
										$log->log($form->getValue('location').' '.$e->getMessage(), Zend_Log::ERR);
								}
							}
						}				
						//CREATE
						//	actually create the event and
						$eventDAO = new Application_Model_Event();
						$id = $eventDAO->insert(array(
							'subject' => $form->getValue("subject"),
							'body' => $form->getValue("body"),
							'location' => $form->getValue("location"),
							'address' => $_formatted_address,
							'event_date' => $form->getValue("date"),
							'event_time' => $form->getValue("time"),
							'event_end' => $form->getValue("time_end"),
							'avatar' => $form->getValue("avatar"),
							'lat' => $_lat,
							'lng' => $_lng
						));
							
						//EVENT TO GROUPS
						//	connect event to groups
						$groupEventDAO = new Application_Model_GroupHasEvent();
						foreach ($form->getValue('groups') as $group_id){
							$groupEventDAO->insert(array(
								'event_id'=>$id,
								'group_id'=>$group_id
							));
						}
						
							
						//SEARCH
						//	create search index for this entry.
						$eventEntryDAO = new Application_Model_Event();
						Ext_Search_Lucene::getInstance()->index( $eventEntryDAO->find($id)->current() );
	
						//REDIRECT / RESPOND
						//	redirect to the even't entry page
						//	or send some data if request is xhr
						if( $this->_request->isXmlHttpRequest() ){
							$this->getHelper('layout')->disableLayout();
							$this->getHelper('viewRenderer')->setNoRender();
							$this->view->event = $eventDAO->find($id)->current();
							$this->_response->setBody($this->view->render("group/partial-event.phtml"));
						}else{
							$this->_redirect("/vidburdir/{$id}");
						}
				//INVALID
				//	the form is invalid
				}else{
					if($this->_request->isXmlHttpRequest()){
						$this->getHelper('layout')->disableLayout();
						$this->getHelper('viewRenderer')->setNoRender();
						$this->_response->setBody($form);
					}else{
						$this->view->form = $form;
					}
				}
			//ACCESS DENIED
			//	user doesn't have access
			}else{
				throw new Zend_Controller_Action_Exception("Access Denide",401);
			}
		//GET
		//	query request
		}else{
			if( $this->_helper->acl->validate( new Ext_Acl_Group($this->_getParam('group_id')), Ext_Acl_Group::RULE_RESOURCE_CREATE ) ){
				if( $this->_request->isXmlHttpRequest() ){
					$this->getHelper('layout')->disableLayout();
					$this->getHelper('viewRenderer')->setNoRender();
					$this->_response->setBody(
						new Application_Form_Event('create',null, ($this->_getParam('group_id'))
							? array( (object)array('group_id'=>$this->_getParam('group_id')) )
							: null 
						)
					);
				}else{
					$this->view->form = new Application_Form_Event('create',null, ($this->_getParam('group_id'))
						? array( (object)array('group_id'=>$this->_getParam('group_id')) )
						: null 
					);
				}
			}else{
				throw new Zend_Controller_Action_Exception("Access Denide",401);
			}
		}
	    */
		
	}

	/**
	 * Update one event.
	 *
	 * @return \Zend\Http\Response|ViewModel
	 */
	public function updateAction(){

        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $mapService = $sm->get('Stjornvisi\Service\Map');



        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if( ($event = $eventService->get( $this->params()->fromRoute('id', 0) )) != false ){

            $groupIds = array_map(function($i){ return $i->id; }, $event->groups );
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );
            //ACCESS GRANTED
            //  user has accss
            if( $access->is_admin || $access->type >= 1 ){

                $form = new EventForm( $groupService->fetchAll() );
                $form->setAttribute('action', $this->url()->fromRoute('vidburdir/update',array('id'=>$event->id)) );

                //POST
                //  http post request
                if( $this->request->isPost() ){
                    $form->setData($this->request->getPost() );


                    //VALID
                    //  form data is valid
                    if( $form->isValid() ){
                        $map = $mapService->request( $form->get('location')->getValue() );
                        $data = $form->getData();
                        unset($data['submit']);
                        $data['lat'] = $map->lat;
                        $data['lng'] = $map->lng;
                        $eventService->update($event->id, $data);
						if( $this->request->isXmlHttpRequest() ){
							$view = new ViewModel(array(
								'event' => $eventService->get($event->id),
								'register_message' => false,
								'logged_in' => $authService->hasIdentity(),
								'access' => $userService->getTypeByGroup(
										($authService->hasIdentity())?$authService->getIdentity()->id:null,
										$groupIds
									),
								'attendees' => $userService->getByEvent($event->id),
							));
							$view->setTemplate('stjornvisi/event/partials/index-event');
							$view->setTerminal(true);
							return $view;
						}else{
							return $this->redirect()->toRoute('vidburdir/index',array('id'=>$event->id));
						}

                    //INVALID
                    //  form data is invalid
                    }else{
                        $view = new ViewModel(array(
                            'form' => $form,
                        ));
						$view->setTerminal( $this->request->isXmlHttpRequest() );
						return $view;
                    }
                //QUERY
                //  http get request
                }else{
					//sleep(200);
                    $form->bind( new \ArrayObject((array)$event) );
					$view = new ViewModel(array(
						'form' => $form
					));
					$view->setTerminal( $this->request->isXmlHttpRequest() );
					return $view;
                }

            //ACCESS DENIED
            }else{
                var_dump('403');
            }


        //NOT FOUND
        //  todo 404
        }else{
            var_dump('404');
        }

	}

	/**
	 * Delete one event.
	 *
	 * @return \Zend\Http\Response
	 */
	public function deleteAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  an event with this ID was found
        if( ($event = $eventService->get( $this->params()->fromRoute('id', 0) )) != false ){
            $groupIds = array_map(function($i){ return $i->id; }, $event->groups );
            $access = $userService->getTypeByGroup(
                ($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
                $groupIds
            );

            //ACCESS GRANTED
            //  user can delete
            if($access->is_admin || $access->type >= 1){
                $eventService->delete( $event->id );
				return $this->redirect()->toRoute('vidburdir');
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
	 * Export attendees list as csv.
	 *
	 * @return \Zend\Stdlib\ResponseInterface
	 */
	public function exportAttendeesAction(){
		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$eventService = $sm->get('Stjornvisi\Service\Event');

		$authService = new AuthenticationService();

		//EVENT FOUND
		//  an event with this ID was found
		if( ($event = $eventService->get( $this->params()->fromRoute('id', 0) )) != false ){

			$groupIds = array_map(function($i){ return $i->id; }, $event->groups );
			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);
			//ACCESS GRANTED
			//  user has accss
			if( $access->is_admin || $access->type >= 1 ){

				$csv = new Csv();
				$csv->setHeader((object)array('Nafn','Titill','Netfang','Dags.'));
				$csv->setName('maertingarlisti'.date('Y-m-d-H:i').'.csv');
				$resultset = $userService->getByEvent($event->id);
				foreach($resultset as $item){
					$csv->add((object)array(
						'name' => $item->name,
						'title' => $item->title,
						'email' => $item->email,
						'register_time' => $item->register_time->format('Y-m-d H:i'),
					));
				}
				$view = new ViewModel();
				$view->setTemplate('layout/csv')
					->setVariable('result_set', $csv)
					->setTerminal(true);
				$output = $this->getServiceLocator()
					->get('viewrenderer')
					->render($view);
				$response = $this->getResponse();
				$headers = $response->getHeaders();
				$headers->addHeaderLine('Content-Type', 'text/csv')
					->addHeaderLine(
						'Content-Disposition',
						sprintf("attachment; filename=\"%s\"",$csv->getName())
					)
					->addHeaderLine('Accept-Ranges', 'bytes')
					->addHeaderLine('Content-Length', strlen($output));

				$response->setContent($output);

				return $response;

			}else{
				var_dump('403');
			}
		}else{
			var_dump('404');
		}






		/*
		//LOGGED IN
		//	user is logged in
		if( Zend_Auth::getInstance()->hasIdentity() ){
			$eventDAO = new Application_Model_Event();
			
			//EVENT FOUND
			//	the event exists
			if( $event = $eventDAO->find($this->_getParam('id'))->current() ){
			
				$eventUsersDAO = new Application_Model_EventUserEntry();
				$this->view->users = $eventUsersDAO->fetchAll(
					"event_id={$event->id} AND attending=".Application_Model_EventHasUser::ATTENDING_YES);
				$this->_helper->layout->disableLayout();
				
				//TODO since this method could deliver the list in many formats,
				//	what is needed here is a clever way to select the correct output
				
				$this->_response->setHeader("Content-type", "text/csv; charset=utf-8");
				$this->_response->setHeader("Content-Disposition", 
					"attachment; filename=\"{$event->event_date}-{$event->subject}.csv\"");
				
			//EVENT NOT FOUND
			//	event does not exist
			}else{
				throw new Zend_Controller_Action_Exception("Resource Not Found",404);
			}
		//NOT LOGGED IN
		//	user is not logged in... 401:Access Denied
		}else{
			throw new Zend_Controller_Action_Exception("Access Denied",401);
		}
		*/
		
	}

	/**
	 * Set if user is going to attend an event or not
	 * This action is listening for the parameter <em>type</em>
	 * that maps 1 to yes and 0 to no.
	 *
	 * @return \Zend\Http\Response
	 */
	public function attendAction(){
        $sm = $this->getServiceLocator();
        $eventService = $sm->get('Stjornvisi\Service\Event');

        $authService = new AuthenticationService();

        //EVENT FOUND
        //  event found in storage
        if( ($event = $eventService->get( $this->params()->fromRoute('id',0) )) ){

            //ACCESS
            //
            if($authService->hasIdentity()){
                $eventService->registerUser(
                    $event->id,
                    $authService->getIdentity()->id,
                    $this->params()->fromRoute('type',0)
                );
				$this->getEventManager()->trigger('notify',$this,array(
					'action' => 'event.attend',
					'recipients' => $authService->getIdentity(),
					'priority' => true,
					'data' => (object)array(
							'event' => $event,
						),
				));

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

	/**
	 * Send mail to members of group(s) of events
	 * @return ViewModel
	 */
	public function sendMailAction(){

		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$eventService = $sm->get('Stjornvisi\Service\Event');

		$authService = new AuthenticationService();

		//EVENT FOUND
		//  an event with this ID was found
		if( ($event = $eventService->get( $this->params()->fromRoute('id', 0) )) != false ){

			$groupIds = array_map(function($i){ return $i->id; }, $event->groups );
			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);
			//ACCESS GRANTED
			//  user has accss
			if( $access->is_admin || $access->type >= 1 ){
				$form = new Email();
				$form->setAttribute('action',$this->url()->fromRoute('vidburdir/send-mail',array(
					'id' => $event->id,
					'type' => $this->params()->fromRoute('type','allir')
				)));
				//POST
				//	post request
				if( $this->request->isPost() ){
					$form->setData($this->request->getPost());
					//VALID
					//	valid form
					if($form->isValid()){

						//NOTIFY
						//	notify user
						//$users = array();
						/*
						if( $this->params()->fromPost('test',false) ){
							$users = array($authService->getIdentity());
							$priority = true;
						}else{
							$users = ( $this->params()->fromRoute('type', 'allir') == 'allir' )
								? $userService->getUserMessageByGroup( $groupIds )
								: $userService->getUserMessageByEvent($event->id) ;
							$priority = false;
						}
						*/
						$this->getEventManager()->trigger('notify',$this,array(
							'action' => \Stjornvisi\Notify\Event::MESSAGING,
							'data' => (object)array(
								'event' => $event,
								'recipients' => ( $this->params()->fromRoute('type', 'allir') == 'allir' ),
								'test' => (bool)$this->params()->fromPost('test',false),
								'subject' => $form->get('subject')->getValue(),
								'body' => $form->get('body')->getValue(),
							),
						));

						return new ViewModel(array(
							'event' => $event,
							'form' => $form,
							'msg' => $this->params()->fromPost('test',false)
									? 'Prufupóstur sendur'
									: 'Póstur sendur',
						));

					//INVALID
					//	invalid form
					}else{
						return new ViewModel(array(
							'event' => $event,
							'form' => $form,
							'msg' => false,
						));
					}

				//QUERY
				//	get request
				}else{
					return new ViewModel(array(
						'event' => $event,
						'form' => $form,
						'msg' => false,
					));
				}
			//ACCESS DENIED
			//	403
			}else{
				var_dump('403');
			}
		}

	}
	
	/**
	 * Serve VCALENDAR VEVENT card
	 * @throws Zend_Controller_Action_Exception
	 */
	public function icalAction(){
	
		$eventDAO = new Application_Model_Event();
		if( ($event=$eventDAO->find($this->_getParam('id'))->current())!=null ){
			$this->_helper->layout->disableLayout();
			$this->_response->setHeader("Content-type", "text/calendar; charset=utf-8");
//			$this->_response->setHeader("Content-type", "text/plain; charset=utf-8");

			$begin_date = null;
			$end_date = null;			
			if( $event->event_date ){
				$begin_time = ($event->event_time) ? $event->event_time : '00:00:00' ;
				$end_time = ($event->event_end) ? $event->event_end : '00:00:00' ;
				$begin_date = new Zend_Date($event->event_date . ' ' . $begin_time , Zend_Date::ISO_8601);
				$end_date = new Zend_Date($event->event_date . ' ' . $end_time , Zend_Date::ISO_8601);
			}else{
				$begin_date = Zend_Date::now();
				$end_date = Zend_Date::now();
			}
			
			$event->event_date = $begin_date;
			$event->event_end = $end_date;
			
			$this->view->event = $event;
			
			
			
		}else{
			throw new Zend_Controller_Action_Exception("Resource Not Found",404);
		}
		
	}


	/**
	 * Get list of even't images.
	 *
	 * @return ViewModel
	 */
	public function galleryListAction(){
		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$eventService = $sm->get('Stjornvisi\Service\Event');

		$authService = new AuthenticationService();

		//EVENT FOUND
		//  an event with this ID was found
		if( ($event = $eventService->get( $this->params()->fromRoute('id', 0) )) != false ){

			$groupIds = array_map(function($i){
				return $i->id;
			},$event->groups);

			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);

			//ACCESS GRANTED
			//
			if( $access->is_admin || $access->type >= 2 ){

				return new ViewModel(array(
					'event' => $event,
					'gallery' => $eventService->getGallery( $event->id )
				));

			//ACCESS DENIED
			//
			}else{
				var_dump('403');
			}
		//NOT FOUND
		//	404
		}else{
			var_dump('404');
		}

	}

	/**
	 * Insert new image into gallery for event.
	 *
	 * @return \Zend\Http\Response|ViewModel
	 */
	public function galleryCreateAction(){
		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$eventService = $sm->get('Stjornvisi\Service\Event');

		$authService = new AuthenticationService();

		//EVENT FOUND
		//  an event with this ID was found
		if( ($event = $eventService->get( $this->params()->fromRoute('id', 0), ($authService->hasIdentity())?$authService->getIdentity()->id:null )) != false ){

			$groupIds = array_map(function($i){
				return $i->id;
			},$event->groups);

			$authService = new AuthenticationService();
			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);

			//ACCESS GRANTED
			//
			if( $access->is_admin || $access->type >= 2 ){
				$form = new GalleryForm();
				$form->setAttribute('action',$this->url()->fromRoute('vidburdir/gallery-create',array('id'=>$event->id)));
				if( $this->request->isPost() ){

					$form->setData($this->request->getPost());
					//FORM VALID
					if($form->isValid()){
						$eventService->addGallery($event->id,$form->getData());
						return $this->redirect()->toRoute('vidburdir/gallery-list',array('id'=>$event->id));
					//FORM INVALID
					//
					}else{
						return new ViewModel(array(
							'access' => $access,
							'event' => $event,
							'form' => $form
						));
					}
				}else{
					return new ViewModel(array(
						'access' => $access,
						'event' => $event,
						'form' => $form
					));
				}

			//ACCESS DENIED
			//	403
			}else{
				var_dump('403');
			}
		//RESOURCE NOT FOUND
		//
		}else{
			var_dump('404');
		}
	}

	/**
	 * Update gallery item.
	 *
	 * @return \Zend\Http\Response|ViewModel
	 */
	public function galleryUpdateAction(){
		$sm = $this->getServiceLocator();
		$eventService = $sm->get('Stjornvisi\Service\Event');
		$userService = $sm->get('Stjornvisi\Service\User');
		$authService = new AuthenticationService();

		//ITEM FOUND
		//
		if( ( $item = $eventService->getGalleryItem( $this->params()->fromRoute('id',0) ) ) != false ){

			$event = $eventService->get( $item->event_id );

			$groupIds = array_map(function($i){
				return $i->id;
			},$event->groups);

			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);

			//ACCESS GRANTED
			//	access granted
			if( $access->is_admin || $access->type >= 2 ){
				$form = new GalleryForm();
				$form->setAttribute('action',$this->url()->fromRoute('vidburdir/gallery-update',array('id'=>$item->id)));

				//POST
				//	post request
				if( $this->request->isPost() ){
					$form->setData( $this->request->getPost() );
					if( $form->isValid() ){
						$eventService->updateGallery($item->id,$form->getData());
						return $this->redirect()->toRoute('vidburdir/gallery-list',array('id'=>$event->id));
					}else{
						return new ViewModel(array(
							'event' => $event,
							'form' => $form
						));
					}
					//QUERY
					//	get request
				}else{
					$form->bind( new \ArrayObject( $item ) );
					return new ViewModel(array(
						'event' => $event,
						'form' => $form
					));
				}
			//ACCESS DENIED
			//
			}else{
				var_dump('403');
			}

		//NOT FOUND
		//	404
		}else{
			var_dump('404');
		}

	}

	/**
	 * Delete one gallery item.
	 *
	 * @return \Zend\Http\Response
	 */
	public function galleryDeleteAction(){
		$sm = $this->getServiceLocator();
		$eventService = $sm->get('Stjornvisi\Service\Event');
		$userService = $sm->get('Stjornvisi\Service\User');
		$authService = new AuthenticationService();

		//ITEM FOUND
		//
		if( ( $item = $eventService->getGalleryItem( $this->params()->fromRoute('id',0) ) ) != false ){

			$event = $eventService->get( $item->event_id );

			$groupIds = array_map(function($i){
				return $i->id;
			},$event->groups);

			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);

			//ACCESS GRANTED
			//	access granted
			if( $access->is_admin || $access->type >= 2 ){
				$eventService->deleteGallery( $item->id );
				return $this->redirect()->toRoute('vidburdir/gallery-list',array('id'=>$event->id));
			//ACCESS DENIED
			//
			}else{
				var_dump('403');
			}

		//NOT FOUND
		//	404
		}else{
			var_dump('404');
		}

	}

	/**
	 * Get list of even't resources.
	 *
	 * @return ViewModel
	 */
	public function resourceListAction(){
		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$eventService = $sm->get('Stjornvisi\Service\Event');

		$authService = new AuthenticationService();

		//EVENT FOUND
		//  an event with this ID was found
		if( ($event = $eventService->get( $this->params()->fromRoute('id', 0) )) != false ){

			$groupIds = array_map(function($i){
				return $i->id;
			},$event->groups);

			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);

			//ACCESS GRANTED
			//
			if( $access->is_admin || $access->type >= 2 ){

				return new ViewModel(array(
					'event' => $event,
					'resources' => $eventService->getResources( $event->id )
				));

				//ACCESS DENIED
				//
			}else{
				var_dump('403');
			}
			//NOT FOUND
			//	404
		}else{
			var_dump('404');
		}

	}

	/**
	 * Insert new resource for event.
	 *
	 * @return \Zend\Http\Response|ViewModel
	 */
	public function resourceCreateAction(){
		$sm = $this->getServiceLocator();
		$userService = $sm->get('Stjornvisi\Service\User');
		$eventService = $sm->get('Stjornvisi\Service\Event');

		$authService = new AuthenticationService();

		//EVENT FOUND
		//  an event with this ID was found
		if( ($event = $eventService->get( $this->params()->fromRoute('id', 0), ($authService->hasIdentity())?$authService->getIdentity()->id:null )) != false ){

			$groupIds = array_map(function($i){
				return $i->id;
			},$event->groups);

			$authService = new AuthenticationService();
			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);

			//ACCESS GRANTED
			//
			if( $access->is_admin || $access->type >= 2 ){
				$form = new ResourceForm();
				$form->setAttribute('action',$this->url()->fromRoute('vidburdir/resource-create',array('id'=>$event->id)));
				if( $this->request->isPost() ){

					$form->setData($this->request->getPost());
					//FORM VALID
					if($form->isValid()){
						$eventService->addResource($event->id,$form->getData());
						return $this->redirect()->toRoute('vidburdir/resource-list',array('id'=>$event->id));
						//FORM INVALID
						//
					}else{
						return new ViewModel(array(
							'access' => $access,
							'event' => $event,
							'form' => $form
						));
					}
				}else{
					return new ViewModel(array(
						'access' => $access,
						'event' => $event,
						'form' => $form
					));
				}

				//ACCESS DENIED
				//	403
			}else{
				var_dump('403');
			}
			//RESOURCE NOT FOUND
			//
		}else{
			var_dump('404');
		}
	}

	/**
	 * Update resource item.
	 *
	 * @return \Zend\Http\Response|ViewModel
	 */
	public function resourceUpdateAction(){
		$sm = $this->getServiceLocator();
		$eventService = $sm->get('Stjornvisi\Service\Event');
		$userService = $sm->get('Stjornvisi\Service\User');
		$authService = new AuthenticationService();

		//ITEM FOUND
		//
		if( ( $item = $eventService->getResourceItem( $this->params()->fromRoute('id',0) ) ) != false ){

			$event = $eventService->get( $item->event_id );

			$groupIds = array_map(function($i){
				return $i->id;
			},$event->groups);

			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);

			//ACCESS GRANTED
			//	access granted
			if( $access->is_admin || $access->type >= 2 ){
				$form = new ResourceForm();
				$form->setAttribute('action',$this->url()->fromRoute('vidburdir/resource-update',array('id'=>$item->id)));

				//POST
				//	post request
				if( $this->request->isPost() ){
					$form->setData( $this->request->getPost() );
					if( $form->isValid() ){
						$eventService->updateResource($item->id,$form->getData());
						return $this->redirect()->toRoute('vidburdir/resource-list',array('id'=>$event->id));
					}else{
						return new ViewModel(array(
							'event' => $event,
							'form' => $form
						));
					}
					//QUERY
					//	get request
				}else{
					$form->bind( new \ArrayObject( $item ) );
					return new ViewModel(array(
						'event' => $event,
						'form' => $form
					));
				}
				//ACCESS DENIED
				//
			}else{
				var_dump('403');
			}

			//NOT FOUND
			//	404
		}else{
			var_dump('404');
		}

	}

	/**
	 * Delete one resource item.
	 *
	 * @return \Zend\Http\Response
	 */
	public function resourceDeleteAction(){
		$sm = $this->getServiceLocator();
		$eventService = $sm->get('Stjornvisi\Service\Event');
		$userService = $sm->get('Stjornvisi\Service\User');
		$authService = new AuthenticationService();

		//ITEM FOUND
		//
		if( ( $item = $eventService->getResourceItem( $this->params()->fromRoute('id',0) ) ) != false ){

			$event = $eventService->get( $item->event_id );

			$groupIds = array_map(function($i){
				return $i->id;
			},$event->groups);

			$access = $userService->getTypeByGroup(
				($authService->hasIdentity()) ? $authService->getIdentity()->id : null,
				$groupIds
			);

			//ACCESS GRANTED
			//	access granted
			if( $access->is_admin || $access->type >= 2 ){
				$eventService->deleteResource( $item->id );
				return $this->redirect()->toRoute('vidburdir/resource-list',array('id'=>$event->id));
				//ACCESS DENIED
				//
			}else{
				var_dump('403');
			}

			//NOT FOUND
			//	404
		}else{
			var_dump('404');
		}

	}

	public function registryDistributionAction(){

		$sm = $this->getServiceLocator();
		$eventService = $sm->get('Stjornvisi\Service\Event');

		$type = $this->params()->fromRoute('type');
		$from = ($this->params()->fromRoute('from'))
			? new DateTime($this->params()->fromRoute('from'))
			: null ;
		$to = ($this->params()->fromRoute('to'))
			? new DateTime($this->params()->fromRoute('to'))
			: null ;
		$result = array();
		switch( $type ){
			case 'klukka':
				$result = $eventService->getRegistrationByHour($from,$to);
				break;
			case 'dagur':
				$result = $eventService->getRegistrationByDayOfWeek($from,$to);
				break;
			case 'manudur':
				$result = $eventService->getRegistrationByDayOfMonth($from,$to);
				break;
			default:
				$result = array();
				break;
		}
		return new JsonModel($result);
	}

	/**
	 * @todo accss controll
	 */
	public function statisticsAction(){

	}
}
