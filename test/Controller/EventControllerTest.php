<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/11/14
 * Time: 16:11
 */

namespace Stjornvisi\Controller;

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\PDOMock;

use Stjornvisi\Bootstrap;
use Stjornvisi\Auth\TestAdapter;
use Zend\Http\Headers;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

use Zend\Authentication\AuthenticationService;
use Zend\Stdlib\Parameters;

class EventControllerTest extends PHPUnit_Extensions_Database_TestCase{

	static private $pdo = null;
	private $conn = null;

	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;
	private $config;

	public function testCreateGlobalEventUserIsNotLoggedIn(){
		$this->routeMatch->setParam('action', 'create');
		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testCreateGlobalEventUserLoggedInButIsNotAdmin(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 2
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testCreateGlobalEventUserLoggedInAndIsAdmin(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testCreateLocalEventUserIsNotLoggedIn(){
		$this->routeMatch->setParam('action', 'create');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testCreateLocalUserLoggedInButIsNotMemberOrAdmin(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 3
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testCreateLocalAndUserIsAdmin(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testCreateLocalAndIsMember(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 4
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testCreateLocalAndUserIsManager(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 5
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testCreateUserCanManageButFormIsInvalid(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');
		$this->routeMatch->setParam('id', 1);

		$request = new Request();
		$request->setMethod(Request::METHOD_POST);
		$request->setPost( new Parameters(array()) );

		$result   = $this->controller->dispatch( $request );
		$response = $this->controller->getResponse();
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testCreateUserCanManageAndTheFormIsValid(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');
		$this->routeMatch->setParam('id', 1);

		$request = new Request();
		$request->setMethod(Request::METHOD_POST);
		$request->setPost( new Parameters(array(
			'subject' => 'subject1',
			'event_date' => '2014-01-01',
			'event_time' => '13:00',
			'event_end' => '13:01',
			'capacity' => '1234'
		)) );

		$result   = $this->controller->dispatch( $request );
		$response = $this->controller->getResponse();
		$this->assertEquals(302, $response->getStatusCode());
	}





	public function testUpdateEntryNotFound(){
		$this->routeMatch->setParam('action', 'update');
		$this->routeMatch->setParam('id', 100);

		$result   = $this->controller->dispatch( $this->request );
		$response = $this->controller->getResponse();
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testUpdateEntryNotProvided(){
		$this->routeMatch->setParam('action', 'update');

		$result   = $this->controller->dispatch( $this->request );
		$response = $this->controller->getResponse();
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testUpdateEntryUserLoggedInButIsNotAMemberOrAdmin(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 2
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'update');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch( $this->request );
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testUpdateUserIsAdminSoHeHasAccess(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'update');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch( $this->request );
		$response = $this->controller->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testUpdateUserIsMemberButHasNoAccess(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 4
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'update');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch( $this->request );
		$response = $this->controller->getResponse();
		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testUpdateUserIsManagerSoHeHasAccess(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 5
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'update');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch( $this->request );
		$response = $this->controller->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testUpdateUserIsChairmanSoHeHasAccess(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 6
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'update');
		$this->routeMatch->setParam('id', 1);

		$result   = $this->controller->dispatch( $this->request );
		$response = $this->controller->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testUpdateUserHasAccessButTheForIsNotValid(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'update');
		$this->routeMatch->setParam('id', 1);

		$request = new Request();
		$request->setMethod( Request::METHOD_POST );
		$request->setPost( new Parameters() );

		$result   = $this->controller->dispatch( $request );
		$response = $this->controller->getResponse();
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testUpdateUserHasAccessAndTheFormIsValid(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'update');
		$this->routeMatch->setParam('id', 1);

		$request = new Request();
		$request->setMethod( Request::METHOD_POST );
		$request->setPost( new Parameters(array(
			'subject' => 'subject1',
			'event_date' => '2014-01-01',
			'event_time' => '13:00',
			'event_end' => '13:01',
			'capacity' => '1234'
		)) );

		$result   = $this->controller->dispatch( $request );
		$response = $this->controller->getResponse();
		$this->assertEquals(302, $response->getStatusCode());
	}

	public function testUpdateUserHasAccessAndTheFormIsValidXhrRequest(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'update');
		$this->routeMatch->setParam('id', 1);

		$request = new Request();
		$request->setHeaders( (new Headers())->addHeaderLine('X-Requested-With','XMLHttpRequest') );
		$request->setMethod( Request::METHOD_POST );
		$request->setPost( new Parameters(array(
			'subject' => 'subject1',
			'event_date' => '2014-01-01',
			'event_time' => '13:00',
			'event_end' => '13:01',
			'capacity' => '1234'
		)) );

		$result   = $this->controller->dispatch( $request );
		$response = $this->controller->getResponse();
		$this->assertEquals(200, $response->getStatusCode());
	}

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 * Performs operation returned by getSetUpOperation().
	 */
	protected function setUp(){

		$serviceManager = Bootstrap::getServiceManager();
		$this->controller = new EventController();
		$this->request    = new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'index'));
		$this->event      = new MvcEvent();
		$this->config = $serviceManager->get('Config');
		$routerConfig = isset($this->config['router']) ? $this->config['router'] : array();
		$router = HttpRouter::factory($routerConfig);

		$this->event->setRouter($router);
		$this->event->setRouteMatch($this->routeMatch);
		$this->controller->setEvent($this->event);
		$this->controller->setServiceLocator($serviceManager);


		$conn = $this->getConnection();
		$conn->getConnection()->query("set foreign_key_checks=0");
		parent::setUp();
		$conn->getConnection()->query("set foreign_key_checks=1");

	}

	/**
	 * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
	 */
	public function getConnection(){

		if( $this->conn === null ){
			if (self::$pdo == null){
				self::$pdo = new PDO(
					$GLOBALS['DB_DSN'],
					$GLOBALS['DB_USER'],
					$GLOBALS['DB_PASSWD'],
					array(
						PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
					));
			}
			$this->conn = $this->createDefaultDBConnection(self::$pdo);
		}

		return $this->conn;
	}

	/**
	 * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	public function getDataSet(){
		return new ArrayDataSet([
			'User' => [
				['id'=>1, 'passwd'=> '', 'is_admin' => 1, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
				['id'=>2, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
				['id'=>3, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
				['id'=>4, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
				['id'=>5, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
				['id'=>6, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
			],
			'Group' => [
				[ 'id'=> 1, 'name' =>'name1','name_short'=>'nameshort1','url'=>'name1']
			],
			'Group_has_User' => [
				['group_id'=>1, 'user_id'=>4, 'type'=>0],
				['group_id'=>1, 'user_id'=>5, 'type'=>1],
				['group_id'=>1, 'user_id'=>6, 'type'=>2],
			],
			'Event' => [
				['id'=>1, 'subject'=>'s1']
			],
			'Group_has_Event' => [
				['event_id'=>1, 'group_id'=>1,'primary'=>0]
			]
		]);
	}
}