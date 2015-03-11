<?php

namespace Stjornvisi\Controller;


use Stjornvisi\View\Model\CsvModel;
use Stjornvisi\View\Model\IcalModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\View\Model\FeedModel;
use Zend\Feed\Writer\Feed;
use Stjornvisi\Form\Group as GroupForm;
use Stjornvisi\Form\Email as GroupEmail;
use Stjornvisi\Lib\Csv;
use \DateTime;
use \DateInterval;
use Zend\Http\Response as HttpResponse;

/**
 * Class GroupController
 *
 * @package Stjornvisi\Controller
 * @author einarvalur
 */
class GroupController extends AbstractActionController{

	/**
	 * Display one group by url-name.
     *
     * @return \Zend\Http\Response|ViewModel
	 */
	public function indexAction(){

        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $newsService = $sm->get('Stjornvisi\Service\News');
        $eventService = $sm->get('Stjornvisi\Service\Event');
        $userService = $sm->get('Stjornvisi\Service\User');

		$auth = new AuthenticationService();
        //GROUP
        //  group found
        if( ($group = $groupService->get( $this->params()->fromRoute('id', 0) )) != false ){

            $yearRange = range(
                $groupService->getFirstYear($group->id),
                ((int)date('n')>=9)?(int)date('Y')+1:(int)date('Y')
            );
            $yearRangeArray = array();
            for($i=0;$i<count($yearRange)-1;$i++){
                $yearRangeArray[] = array_slice($yearRange,$i,2);
            }
			$yearRangeArray = array_reverse($yearRangeArray);
            $from = null;
            $to = null;
            //CLIENT DEFINED RANGE
            //  user wants to select the range
            if( $this->params()->fromRoute('range', '') != '' ){
                $rangeArray = explode('-',$this->params()->fromRoute('range', ''));
                $from = new \DateTime($rangeArray[0].'-09-01 00:00:00');
                $to = new \DateTime($rangeArray[1].'-08-31 23:59:00');
            //DEFAULT RANGE
            //  use the current stjornvisi calendar
            }else{
                //GET CURRENT MONTH INT
                $monthInt = (int)date('n');
                $yearInt = (int)date('Y');

                //IN THE 2nd HALF
                //  user is in the second half of the
                //  stjornvisi calendar
                if( $monthInt >= 9 ){
                    $from = new \DateTime($yearInt.'-09-01 00:00:00');
                    $to = new \DateTime(($yearInt+1).'-08-31 23:59:00');
                //IN THE 1st HALF
                //  user is in the first half of the
                //  stjornvisi calendar
                }else{
                    $from = new \DateTime(($yearInt-1).'-09-01 00:00:00');
                    $to = new \DateTime(($yearInt).'-08-31 23:59:00');
                }
            }


            return new ViewModel(array(
                'range' => (object)array('from'=>$from, 'to'=>$to, 'range'=>$yearRangeArray),
                'group' => $group,
                'news' => $newsService->getRangeByGroup( $group->id, $from, $to ),
                'events' => $eventService->getRangeByGroup(
						$group->id, $from, $to, ($auth->hasIdentity())?$auth->getIdentity()->id:null ),
                'chairmen' => $userService->getByGroup( $group->id,2 ),
                'managers' => $userService->getByGroup( $group->id,1 ),
                'users' => $userService->getByGroup( $group->id, 0 ),
                'access' => $userService->getTypeByGroup(
						$auth->hasIdentity()?$auth->getIdentity()->id:null,
						$group->id
					),
				'logged_in' => $auth->hasIdentity()
            ));

        //NO GROUP
        //  this group ID not found
        }else{
			return $this->notFoundAction();
        }

	}

    /**
     * Create new Group.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function createAction(){

        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');

        $auth = new AuthenticationService();

        $access = $userService->getTypeByGroup(
            ( $auth->hasIdentity() ) ? $auth->getIdentity()->id : null ,
            0
        );

        //ACCESS GRANTED
        //  user is admin
        if( $access->is_admin ){

            $form = new GroupForm();
			$form->setAttribute('action', $this->url()->fromRoute('hopur/create'));

            //POST
            //  http post request
            if( $this->request->isPost() ){
                $form->setData( $this->request->getPost() );

                //VALID
                //  valid form
                if( $form->isValid() ){
                    $sm = $this->getServiceLocator();
                    $groupService = $sm->get('Stjornvisi\Service\Group');
					/** @var  $groupService \Stjornvisi\Service\Group */

                    //CREATE
                    //  create record and get ID back
                    $id = $groupService->create( $form->getData() );
					$group = $groupService->get($id);
                    return $this->redirect()->toRoute('hopur/index',array('id'=>$group->url));
                //INVALID
                //  invalid form
                }else{
					$this->getResponse()->setStatusCode(400);
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
        //  user not admin
        }else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
        }
	}

    /**
     * Update Group.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function updateAction(){

        //SERVICE
        //  get group service
        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $userService = $sm->get('Stjornvisi\Service\User');

        //ITEM FOUND
        //  item is in storage
        if( ($group = $groupService->get($this->params()->fromRoute('id', 0))) != null ){

            //ACCESS CONTROL
            //  if user is admin or manager
            $auth = new AuthenticationService();
            $access = $userService->getTypeByGroup(
                ( $auth->hasIdentity() ) ? $auth->getIdentity()->id : null ,
                $group->id
            );
            if( $access->is_admin || $access->type >= 1 ){

                //POST
                //  http post query
                if($this->request->isPost()){
					$form = new GroupForm();
					$form->setAttribute('action',$this->url()->fromRoute('hopur/update',array('id'=>$group->url)) );
                    $form->setData($this->request->getPost() );


                    if( $form->isValid() ){
                        //DATA
                        //  extract data from form and do some
                        //  small alterations on it. (add one field 'url'
                        //  and remove one 'submit')
                        $data = $form->getData();

                        //CREATE
                        //  create record and get ID back
                        $groupService->update( (int)$group->id, $data );
						$group = $groupService->get($group->id);

                        return $this->redirect()->toRoute('hopur/index',array('id'=>$group->url));

                    }else{
						$this->getResponse()->setStatusCode(400);
                        return new ViewModel(array(
                            'form' => $form
                        ));
                    }

                //QUERY
                //  http get request
                }else{
                    $form = new GroupForm();
                    $form->bind( new \ArrayObject((array)$group) );
                    $form->setAttribute('action', $this->url()->fromRoute('hopur/update',array('id'=>$group->url)) );
                    return new ViewModel(array(
                        'form' => $form
                    ));
                }
            //ACCESS DENIED
            //  user is not admin or manager
            }else{
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
            }

        //ITEM NOT FOUND
        //  404
        }else{
			return $this->notFoundAction();
        }
    }

    /**
     * Delete Group.
     *
     */
    public function deleteAction(){
        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $groupService = $sm->get('Stjornvisi\Service\Group');

        //GROUP FOUND
        //  item found in storage
        if( ($group = $groupService->get( $this->params()->fromRoute('id', 0) )) != null ){
            $auth = new AuthenticationService();
            $access = $userService->getTypeByGroup(
                ( $auth->hasIdentity() ) ? $auth->getIdentity()->id : null ,
                0
            );
            //ACCESS GRANTED
            //  user is manager or admin
            if($access->is_admin || $access->type >= 1){
                $groupService->delete($group->id);
			//ACCESS DENIED
			//  user doesn't have access
            }else{
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
            }
		//GROUP NOT FOUND
		//  no group with this url
        }else{
			return $this->notFoundAction();
        }
    }

    /**
     * List all groups in order.
     *
     * @return ViewModel
     */
    public function listAction(){
        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');
        return new ViewModel(array(
            'groups_all' => $groupService->fetchAllExtended(1),
        ));
    }

    /**
     * User is requesting to join/leave a group.
     *
     */
    public function registerAction(){

        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');

        $auth = new AuthenticationService();
        //IS LOGGED IN
        //  user is logged in, we have his/her ID
        if( $auth->hasIdentity() ){
            //GROUP FOUND
            //  user can register/unregister
            if( ( $group = $groupService->get($this->params()->fromRoute('id', 0)) ) != null ){
                $groupService->registerUser(
                    $group->id,
                    $auth->getIdentity()->id,
                    (bool)$this->params()->fromRoute('type', 0)
                );

				//NOTIFY
				//	notify user
				$this->getEventManager()->trigger('notify',$this,array(
					'action' => 'Stjornvisi\Notify\Submission',
					'data' => (object)array(
						'recipient' => $auth->getIdentity()->id,
						'group_id' => $group->id,
						'register' => (bool)$this->params()->fromRoute('type', 0)
					),
				));
                return $this->redirect()->toRoute('hopur/index',array('id'=>$group->url));
            //GROUP NOT FOUND
            //	resource not found
            }else{
				return $this->notFoundAction();
            }
        //IS NOT LOGGED IN
        //  user is not logged in
        }else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
        }

    }

	/**
	 * Register or unregister to email
	 * notifications from a group.
	 *
	 * @return array|ViewModel
	 */
	public function registerMailAction(){
		$sm = $this->getServiceLocator();
		$groupService = $sm->get('Stjornvisi\Service\Group');
		/** @var $groupService \Stjornvisi\Service\Group */

		$auth = new AuthenticationService();

		if( $auth->hasIdentity() ){
			if( ($group = $groupService->get( $this->params()->fromRoute('id',0) )) != false ){
				$groupService->registerMailUser(
					$group->id,
					$auth->getIdentity()->id,
					(bool)( (int)$this->params()->fromRoute('type',0) )
				);
			}else{
				return $this->notFoundAction();
			}
		}else{
			$this->getResponse()->setStatusCode(401);
			$model = new ViewModel();
			$model->setTemplate('error/401');
			return $model;
		}
	}

    /**
     * Set the status of a user in a group:
     *  chairman
     *  manager
     *  member
     *
     * @return \Zend\Http\Response
     */
    public function userStatusAction(){

        $sm = $this->getServiceLocator();
        $userService = $sm->get('Stjornvisi\Service\User');
        $groupService = $sm->get('Stjornvisi\Service\Group');

        //GROUP FOUND
        //  item found in storage
        if( ( $group = $groupService->get($this->params()->fromRoute('id', 0)) ) != null ){
            $auth = new AuthenticationService();
            $access = $userService->getTypeByGroup(
                ( $auth->hasIdentity() ) ? $auth->getIdentity()->id : null ,
                $group->id
            );
            //ACCESS GRANTED
            //  user has access
            if( $access->is_admin || $access->type >= 1 ){
                $groupService->userStatus(
                    $group->id,
                    $this->params()->fromRoute('user_id', 0),
                    $this->params()->fromRoute('type', 0)
                );
                return $this->redirect()->toRoute('hopur/index',array('id'=>$group->url));
            //ACCESS DENIED
            //	access denied
            }else{
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
            }

        //GROUP NOT FOUND
        //  item not found in storage
        }else{
			return $this->notFoundAction();
        }

	}

	/**
	 * Export members list in CSV
	 *
	 * @return array|CsvModel|ViewModel
	 */
	public function exportMembersAction(){
        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $userService = $sm->get('Stjornvisi\Service\User');

        //GROUP
        //  group found
        if( ($group = $groupService->get( $this->params()->fromRoute('id', 0) )) != false ){
            $auth = new AuthenticationService();
            $access = $userService->getTypeByGroup(
                ( $auth->hasIdentity() ) ? $auth->getIdentity()->id : null ,
                $group->id
            );
            //ACCESS GRANTED
            //  user has access
            if( $access->is_admin || $access->type >= 1 ){

				$csv = new Csv();
				$csv->setHeader(array(
					'Nafn',
					'Netfang',
					'Titill',
					'Dags.',
					'Hlutverk'
				));
				$csv->setName('medlimalisti'.date('Y-m-d-H:i').'.csv');
				$resultset = $userService->getByGroup( $group->id  );
				foreach( $resultset as $result ){
					$type = '';
					switch($result->type){
						case 0:
							$type = 'Meðlimur';
							break;
						case 1:
							$type = 'Stjórnandi';
							break;
						case 2:
							$type = 'Formaður';
							break;
						default:
							$type = 'Meðlimur';
							break;
					}
					$csv->add(array(
						'name' => $result->name,
						'email' => $result->email,
						'title' => $result->title,
						'created_date' => $result->created_date->format('Y-m-d'),
						'type' => $type
					));
				}

				$model = new CsvModel();
				$model->setData( $csv );

				return $model;

            //ACCESS DENIED
            //  user has no access
            }else{
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
            }
        //NO GROUP
        //  group not found
        //TODO 404
        }else{
			return $this->notFoundAction();
        }
	}

	/**
	 * Export events list in CSV
	 *
	 * @return array|CsvModel|ViewModel
	 */
	public function exportEventsAction(){
		$sm = $this->getServiceLocator();
		$groupService = $sm->get('Stjornvisi\Service\Group');
		$userService = $sm->get('Stjornvisi\Service\User');
		$eventService = $sm->get('Stjornvisi\Service\Event');
		/** @var $eventService \Stjornvisi\Service\Event */



		//GROUP
		//  group found
		if( ($group = $groupService->get( $this->params()->fromRoute('id', 0) )) != false ){
			$auth = new AuthenticationService();
			$access = $userService->getTypeByGroup(
				( $auth->hasIdentity() ) ? $auth->getIdentity()->id : null ,
				$group->id
			);
			//ACCESS GRANTED
			//  user has access
			if( $access->is_admin || $access->type >= 1 ){

				$server = isset( $_SERVER['HTTP_HOST'] )
					? "http://".$_SERVER['HTTP_HOST']
					: 'http://0.0.0.0' ;

				$csv = new Csv();
				$csv->setHeader(array(
					'Nafn',
					'Hópar',
					'Dags.',
					'Slóð',
				));
				$csv->setName('vidburdalistilisti'.date('Y-m-d-H:i').'.csv');
				$events = $eventService->getByGroup( $group->id );
				foreach( $events as $result ){

					$csv->add(array(
						'name' => $result->subject,
						'groups' => implode(', ',array_map(function($item){
							return $item->name_short;
						},$result->groups)),
						'date' => $result->event_date->format('Y-m-d'),
						'url' => $server.$this->url()->fromRoute('vidburdir/index',array('id'=>$result->id))
					));
				}

				$model = new CsvModel();
				$model->setData( $csv );

				return $model;

				//ACCESS DENIED
				//  user has no access
			}else{
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
			}
			//NO GROUP
			//  group not found
			//TODO 404
		}else{
			return $this->notFoundAction();
		}
	}

    /**
     * RSS feed for all events
     *
     * Get all events from two months back in time
     * @todo Re-thing the date range
     */
    public function rssEventsAction(){

        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');

        //ITEM FOUND
        //  item is in storage
        if( ($group = $groupService->get($this->params()->fromRoute('name', 0))) != null ){
			$server = isset($_SERVER['HTTP_HOST'])
				? $_SERVER['HTTP_HOST']
				: '0.0.0.0';
            $eventService = $sm->get('Stjornvisi\Service\Event');
            $from = new DateTime();
            $from->sub( new DateInterval('P2M') );
            $to = new DateTime();
            $to->add( new DateInterval('P2M') );

            $feed = new Feed();
            $feed->setTitle("Viðburðir {$group->name}");
            $feed->setFeedLink("http://{$server}", 'atom');
            $feed->addAuthor(array(
                'name'  => 'Stjórnvísi',
                'email' => 'stjornvisi@stjornvisi.is',
                'uri'   => "http://{$server}",
            ));
            $feed->setDescription('Viðburðir');
            $feed->setLink("http://{$_SERVER['HTTP_HOST']}");
            $feed->setDateModified(new DateTime());

            $data = array();

            foreach($eventService->getRangeByGroup($group->id, $from, $to ) as $row){
                //create entry...
                $entry = $feed->createEntry();
                $entry->setTitle($row->subject);
                $entry->setLink( "http://{$_SERVER['HTTP_HOST']}/vidburdir/{$row->id}" );
                $entry->setDescription($row->body.'.');

                $entry->setDateModified($row->event_date);
                $entry->setDateCreated($row->event_date);

                $feed->addEntry($entry);
            }

            $feed->export('rss');

            $feedmodel = new FeedModel();
            $feedmodel->setFeed($feed);

            return $feedmodel;
        //ITEM NOT FOUND
        //  item wasn't found
        }else{
			return $this->notFoundAction();
        }
    }

    /**
     * RSS feed for all news
     *
     * Get all news from two months back in time
     * @todo 404
     */
    public function rssNewsAction(){
        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');

        //ITEM FOUND
        //  item is in storage
        if( ($group = $groupService->get($this->params()->fromRoute('name', 0))) != null ){
            $sm = $this->getServiceLocator();
            $newsService = $sm->get('Stjornvisi\Service\News');
            $from = new DateTime();
            $from->sub( new DateInterval('P2M') );
            $to = new DateTime();
            $to->add( new DateInterval('P2M') );

            $feed = new Feed();
            $feed->setTitle('Feed Example');
            $feed->setFeedLink('http://ourdomain.com/rss', 'atom');
            $feed->addAuthor(array(
                'name'  => 'Stjórnvísi',
                'email' => 'stjornvisi@stjornvisi.is',
                'uri'   => 'http://stjornvisi.is',
            ));
            $feed->setDescription('Fréttir');
            $feed->setLink('http://ourdomain.com');
            $feed->setDateModified(new DateTime());

            $data = array();

            foreach($newsService->getRangeByGroup($group->id, $from, $to ) as $row){
                //create entry...
                $entry = $feed->createEntry();
                $entry->setTitle($row->title);
                $entry->setLink( 'http://stjornvisi.is/' );
                $entry->setDescription($row->body.'.');

                $entry->setDateModified($row->created_date);
                $entry->setDateCreated($row->created_date);

                $feed->addEntry($entry);
            }

            $feed->export('rss');

            $feedmodel = new FeedModel();
            $feedmodel->setFeed($feed);

            return $feedmodel;
        //ITEM NOT FOUND
        //
        }else{
			return $this->notFoundAction();
        }
    }

    /**
     * Send mail to all leaders og all persons in group
     * @todo refactor
     */
    public function sendMailAction(){
        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');
        $userService = $sm->get('Stjornvisi\Service\User');

        //ITEM FOUND
        //  item is in storage
        if( ($group = $groupService->get($this->params()->fromRoute('id', 0))) != null ){

            //AUTHENTICATION
            //  get authentication service
            $auth = new AuthenticationService();
            $access = $userService->getTypeByGroup(
                ( $auth->hasIdentity() ) ? $auth->getIdentity()->id : null ,
                $group->id
            );

            //ACCESS
            //  user has access
            if( $access->is_admin || $access->type >= 1 ){

                //POST
                //  post request
                if($this->request->isPost()){

					$post = $this->getRequest()->getPost(); /** @var $post \ArrayObject */


                    $form = new GroupEmail();
                    $form->setData($post );
                    $form->setAttribute(
                        'action',
						$this->url()->fromRoute('hopur/send-mail',array(
							'id'=>$group->url,
							'type'=> $this->params()->fromRoute('type', 'allir')
							)
						)
                    );

					//VALID
					//	form is valid
					if( $form->isValid() ){

						//TEST
						//	send out test e-mail
						if( $post->offsetGet('test') ){

							$this->getEventManager()->trigger('notify',$this,array(
								'action' => 'Stjornvisi\Notify\Group',
								'data' => (object)array(
										'group_id' => $group->id,
										'recipients' => ( $this->params()->fromRoute('type', 'allir') ),
										'test' => true,
										'subject' => $form->get('subject')->getValue(),
										'body' => $form->get('body')->getValue(),
										'sender_id' => (int)$auth->getIdentity()->id
									),
							));
							return new ViewModel(array(
								'form' => $form,
								'msg' => "Prufupóstur hefur verið sendur á {$auth->getIdentity()->email}",
							));

						//SEND
						//	send out full e-mail
						}else{
							$this->getEventManager()->trigger('notify',$this,array(
								'action' => 'Stjornvisi\Notify\Group',
								'data' => (object)array(
										'group_id' => $group->id,
										'recipients' => ( $this->params()->fromRoute('type', 'allir') ),
										'test' => false,
										'subject' => $form->get('subject')->getValue(),
										'body' => $form->get('body')->getValue(),
										'sender_id' => (int)$auth->getIdentity()->id
									),
							));

							return new ViewModel(array(
								'form' => null,
								'msg' => 'Póstur sendur',
							));
						}

					//INVALID
					// the form is invalid
					}else{
						return new ViewModel(array(
							'form' => $form,
							'msg' => '',
						));
					}

                //QUERY
                //  get request
                }else{
                    $from = new GroupEmail();
                    $from->setAttribute('action',$this->url()->fromRoute('hopur/send-mail',array(
							'id'=>$group->url,
							'type'=> $this->params()->fromRoute('type', 'allir')
						)
					));
                    return new ViewModel(array(
                        'form' => $from
                    ));
                }
            //NO ACCESS
            }else{
				$this->getResponse()->setStatusCode(401);
				$model = new ViewModel();
				$model->setTemplate('error/401');
				return $model;
            }

        //ITEM NOT FOUND
        //
        }else{
			return $this->notFoundAction();
        }

    }

	/**
	 * Get statistics for al groups.
	 *
	 * @return JsonModel
	 * @todo not fully implemented
	 */
	public function eventStatisticsAction(){
		$sm = $this->getServiceLocator();
		$groupService = $sm->get('Stjornvisi\Service\Group');

		return new JsonModel(
			$groupService->fetchEventStatistics()
		);
	}

	/**
	 * @return JsonModel
	 * @todo not fully implemented
	 */
	public function memberStatisticsAction(){
		$sm = $this->getServiceLocator();
		$groupService = $sm->get('Stjornvisi\Service\Group');

		return new JsonModel(
			$groupService->fetchMemberStatistics()
		);
	}

	/**
	 * @return JsonModel
	 * @todo not fully implemented
	 */
	public function statisticsAction(){

	}

	/**
	 * Generate iCal document one year back in tme from
	 * currenr date.
	 *
	 * @return IcalModel
	 */
	public function calendarAction(){
		$sm = $this->getServiceLocator();
		$groupService = $sm->get('Stjornvisi\Service\Group');
		$eventService = $sm->get('Stjornvisi\Service\Event');
		/** @var $eventService \Stjornvisi\Service\Event */

		if( ( $group = $groupService->get($this->params()->fromRoute('id',0) )) != null ){
			$date = new DateTime();
			$date->sub( new DateInterval('P12M') );

			return new IcalModel(array(
				'events' =>$eventService->getRangeByGroup( $group->id, $date )
			));
		}else{
			return $this->notFoundAction();
		}

	}
}
