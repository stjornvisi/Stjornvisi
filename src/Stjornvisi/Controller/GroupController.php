<?php

namespace Stjornvisi\Controller;


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
     * @todo 404
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
                'events' => $eventService->getRangeByGroup( $group->id, $from, $to ),
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
            var_dump($group);
        }

	}

    /**
     * Create new Group.
     *
     * @return \Zend\Http\Response|ViewModel
     * @todo 404 / 403
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

            //POST
            //  http post request
            if( $this->request->isPost() ){
                $form->setData($this->request->getPost() );

                //VALID
                //  valid form
                if( $form->isValid() ){
                    $sm = $this->getServiceLocator();
                    $groupService = $sm->get('Stjornvisi\Service\Group');

                    //DATA
                    //  extract data from form and do some
                    //  small alterations on it. (add one field 'url'
                    //  and remove one 'submit')
                    $data = $form->getData();

                    //CREATE
                    //  create record and get ID back
                    $id = $groupService->create( $data );
					$group = $groupService->get($id);
                    return $this->redirect()->toRoute('hopur/index',array('id'=>$group->url));
                //INVALID
                //  invalid form
                }else{
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
        //TODO 403
        }else{
            var_dump('access denied');
        }
	}

    /**
     * Update Group.
     *
     * @todo 404 / 403
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
            //TODO 403
            }else{
                var_dump('access denied');
            }

        //ITEM NOT FOUND
        //  404
        //TODO 404
        }else{
            var_dump('item not found');
        }
    }

    /**
     * Delete Group.
     *
     * @todo 404 / 403
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
                //TODO  403
            }else{
                var_dump('403');
            }
            //GROUP NOT FOUND
            //  no group with this url
            //TODO 404
        }else{
            var_dump('item not found');
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
            'groups_all' => $groupService->fetchAllExtended(),
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
					'action' => \Stjornvisi\Notify\Submission::REGISTER,
					'data' => (object)array(
						'recipient' => $auth->getIdentity()->id,
						'group_id' => $group->id,
						'register' => (bool)$this->params()->fromRoute('type', 0)
					),
				));
                return $this->redirect()->toRoute('hopur/index',array('id'=>$group->url));
            //GROUP NOT FOUND
            //TODO 404
            }else{
                var_dump('404');
            }
        //IS NOT LOGGED IN
        //  user is not logged in
        }else{
            var_dump(403);
        }

    }

    /**
     * Set the status of a user in a group:
     *  chairman
     *  manager
     *  member
     *
     * @todo 404 / 403
     * @todo send an email to tell them about new status
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
            //TODO 403
            }else{
                var_dump('403');
            }

            //GROUP NOT FOUND
        //  item not found in storage
        //todo 404
        }else{
            var_dump('404');
        }

	}

    /**
     * Export members list in CSV
     *
     * @return ViewModel
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
				$csv->setHeader((object)array(
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
					$csv->add((object)array(
						'name' => $result->name,
						'email' => $result->email,
						'title' => $result->title,
						'created_date' => $result->created_date->format('Y-m-d'),
						'type' => $type
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
						sprintf("attachment; filename=\"%s\"", $csv->getName())
					)
					->addHeaderLine('Accept-Ranges', 'bytes')
					->addHeaderLine('Content-Length', strlen($output));
				$response->setContent($output);

				return $response;

            //ACCESS DENIED
            //  user has no access
            //TODO 403
            }else{
                var_dump('403');
            }
        //NO GROUP
        //  group not found
        //TODO 404
        }else{
            var_dump('404');
        }
	}

    /**
     * RSS feed for all events
     *
     * Get all events from two months back in time
     * @todo Re-thing the date range
     * @todo 404
     */
    public function rssEventsAction(){

        $sm = $this->getServiceLocator();
        $groupService = $sm->get('Stjornvisi\Service\Group');

        //ITEM FOUND
        //  item is in storage
        if( ($group = $groupService->get($this->params()->fromRoute('name', 0))) != null ){
            $eventService = $sm->get('Stjornvisi\Service\Event');
            $from = new DateTime();
            $from->sub( new DateInterval('P2M') );
            $to = new DateTime();
            $to->add( new DateInterval('P2M') );

            $feed = new Feed();
            $feed->setTitle("Viðburðir {$group->name}");
            $feed->setFeedLink($_SERVER['HTTP_REFERER'], 'atom');
            $feed->addAuthor(array(
                'name'  => 'Stjórnvísi',
                'email' => 'stjornvisi@stjornvisi.is',
                'uri'   => "http://{$_SERVER['HTTP_HOST']}",
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
            var_dump('404');
        }


    }

    /**
     * RSS feed for all news
     *
     * Get all news from two months back in time
     * @todo Re-thing the date range
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
            var_dump('404');
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

                    $form = new GroupEmail();
                    $form->setData($this->request->getPost() );
                    $form->setAttribute(
                        'action',
						$this->url()->fromRoute('hopur/send-mail',array(
							'id'=>$group->url,
							'type'=> $this->params()->fromRoute('type', 'allir')
							)
						)
                    );

					//NOTIFY
					//	notify user
					$users = array();
					$priority = false;
					if( $this->params()->fromPost('test',false) ){
						$users = array($auth->getIdentity());
						$priority = true;
					}else{
						$users = ( $this->params()->fromRoute('type', 'allir') == 'formenn' )
							? $userService->getUserMessageByGroup( array($group->id), array(0) )
							: $userService->getUserMessageByGroup( array($group->id) );
						$priority = false;
					}
					$this->getEventManager()->trigger('notify',$this,array(
						'action' => 'group.message',
						'recipients' => $users,
						'priority' => $priority,
						'data' => (object)array(
							'group' => $group,
							'subject' => $form->get('subject')->getValue(),
							'body' => $form->get('body')->getValue(),
						),
					));

					return new ViewModel(array(
						'form' => isset($post['test'])?$form:null,
						'msg' => isset($post['test'])?'Prufupóstur sendur':'Póstur sendur',
					));

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
                        'form' => new GroupEmail()
                    ));
                }
            //NO ACCESS
            }else{
                var_dump('403');
            }

        //ITEM NOT FOUND
        //
        }else{
            var_dump('404');
        }

    }

	/**
	 * Get statistics for al groups.
	 *
	 * @return JsonModel
	 */
	public function eventStatisticsAction(){
		$sm = $this->getServiceLocator();
		$groupService = $sm->get('Stjornvisi\Service\Group');

		return new JsonModel(
			$groupService->fetchEventStatistics()
		);
	}

	public function memberStatisticsAction(){
		$sm = $this->getServiceLocator();
		$groupService = $sm->get('Stjornvisi\Service\Group');

		return new JsonModel(
			$groupService->fetchMemberStatistics()
		);
	}

	public function statisticsAction(){

	}
}
