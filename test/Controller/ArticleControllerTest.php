<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/11/14
 * Time: 16:11
 */

namespace Stjornvisi\Controller;

require_once __DIR__.'/../ArrayDataSet.php';
require_once __DIR__.'/../PDOMock.php';

use \PDO;
use \PHPUnit_Extensions_Database_TestCase;
use Stjornvisi\ArrayDataSet;
use Stjornvisi\PDOMock;

use Stjornvisi\Bootstrap;
use Stjornvisi\Auth\TestAdapter;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

use Zend\Authentication\AuthenticationService;
use Zend\Stdlib\Parameters;

class ArticleControllerTest extends PHPUnit_Extensions_Database_TestCase{

	static private $pdo = null;
	private $conn = null;

	protected $controller;
	protected $request;
	protected $response;
	protected $routeMatch;
	protected $event;
	private $config;


	public function testListAction(){

		$this->routeMatch->setParam('action', 'list');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testIndexActionNoEntryProvided(){

		$this->routeMatch->setParam('action', 'index');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testIndexActionEntryOutOfBounce(){

		$this->routeMatch->setParam('action', 'index');
		$this->routeMatch->setParam('id', '100');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testIndexActionWithEntry(){

		$this->routeMatch->setParam('action', 'index');
		$this->routeMatch->setParam('id', '1');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testCreateUnauthorized(){

		$this->routeMatch->setParam('action', 'create');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(401, $response->getStatusCode());
	}

	public function testCreateAuthorized(){

		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1,
			'is_admin' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');

		$result   = $this->controller->dispatch($this->request);
		$response = $this->controller->getResponse();

		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testForm01(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1,
			'is_admin' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');

		$request = new Request();
		$request->setMethod( Request::METHOD_POST );
		$request->setPost( (new Parameters(array())) );

		$result   = $this->controller->dispatch($request);
		$response = $this->controller->getResponse();

		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testForm02(){
		$auth = new AuthenticationService();
		$result = $auth->authenticate( new TestAdapter((object)[
			'id' => 1,
			'is_admin' => 1
		]) );
		$result->isValid();

		$this->routeMatch->setParam('action', 'create');

		$request = new Request();
		$request->setMethod( Request::METHOD_POST );
		$request->setPost( (new Parameters(array(
			'title' => 'Hani krummi',
			'summary' => 'Hani krummi',
			'body' => 'Hani krummi',
			'venue' => 'Hani krummi',
			'authors' => [1,2,3],
		))) );

		$result   = $this->controller->dispatch($request);
		$response = $this->controller->getResponse();

		//print_r($result->form->getMessages());

		$this->assertEquals(302, $response->getStatusCode());
	}

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 * Performs operation returned by getSetUpOperation().
	 */
	protected function setUp(){

		$serviceManager = Bootstrap::getServiceManager();
		$this->controller = new ArticleController();
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
					$this->config['db']['dns'],
					$this->config['db']['user'],
					$this->config['db']['password'],
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
			'Article' => [
				['id'=>1,'title'=>'t1','body'=>'b1','summary'=>'s1','created'=>date('Y-m-d'),'published'=>date('Y-m-d'),'venue'=>'v1'],
				['id'=>2,'title'=>'t2','body'=>'b2','summary'=>'s2','created'=>date('Y-m-d'),'published'=>date('Y-m-d'),'venue'=>'v2'],
				['id'=>3,'title'=>'t3','body'=>'b3','summary'=>'s3','created'=>date('Y-m-d'),'published'=>date('Y-m-d'),'venue'=>'v3'],
			],
			'Author' => [
				['id'=>1,'name'=>'n1','avatar'=>'a1','info'=>'i1'],
				['id'=>2,'name'=>'n2','avatar'=>'a2','info'=>'i2'],
				['id'=>3,'name'=>'n3','avatar'=>'a3','info'=>'i3'],
			],
			'Author_has_Article' => [
				['author_id' => 1, 'article_id' => 1],
			],
		]);
	}
}