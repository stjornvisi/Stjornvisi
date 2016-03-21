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
use Stjornvisi\DataHelper;
use Stjornvisi\Form\Group;

use Stjornvisi\Bootstrap;
use Stjornvisi\Auth\TestAdapter;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;

use Zend\Authentication\AuthenticationService;
use Zend\Stdlib\Parameters;

class GroupControllerTest extends PHPUnit_Extensions_Database_TestCase
{

    static private $pdo = null;
    private $conn = null;

    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
    private $config;


    public function testOnlyAdminCanCreateGroupButThisUserIsNotLoggedIn()
    {
        $this->routeMatch->setParam('action', 'create');
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testOnlyAdminCanCreateGroupAndThisIsNotAdmin()
    {
        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 2
        ]));
        $result->isValid();

        $this->routeMatch->setParam('action', 'create');
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testOnlyAdminCanCreateGroupAndThisIsAValidAdmin()
    {
        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 1
        ]));
        $result->isValid();

        $this->routeMatch->setParam('action', 'create');
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateWithInvalidPostArguments()
    {
        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 1
        ]));
        $result->isValid();

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost((new Parameters(array(
            'name' => 'Þetta er svo langt nafn að ég veit eiginlega ekki hvað ég á að gera við það',
            'name_short' => '0',
        ))));

        $this->routeMatch->setParam('action', 'create');
        $result   = $this->controller->dispatch($request);
        //print_r( $result->form->getMessages() );
        $response = $this->controller->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateWithValidFormArguments()
    {
        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 1
        ]));
        $result->isValid();

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost((new Parameters($this->getValidPost())));

        $this->routeMatch->setParam('action', 'create');
        $result   = $this->controller->dispatch($request);
        $msg = null;
        if ($result && isset($result->form)) {
            /** @var Group $form */
            $form = $result->form;
            $messages = $form->getMessages();
            $msg = $this->messages($messages);
        }
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode(), $msg);
    }

    private function getValidPost()
    {
        return DataHelper::newGroup();
    }

    private function messages($messages)
    {
        $ret = [];
        foreach ($messages as $element => $elementMessages) {
            $ret[] = "$element: " . implode('; ', $elementMessages);
        }
        return implode("\n", $ret);
    }

    public function testUpdateButTheEntryIsNotFound()
    {
        $this->routeMatch->setParam('action', 'update');
        $this->routeMatch->setParam('id', 'name2');

        $result   = $this->controller->dispatch(new Request());
        $response = $this->controller->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testOnlyAdminCanUpdateGroupButThisUserIsNotLoggedIn()
    {
        $this->routeMatch->setParam('action', 'update');
        $this->routeMatch->setParam('id', 'name1');

        $result   = $this->controller->dispatch(new Request());
        $response = $this->controller->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testAdminCanUpdateButTheFormIsNotValid()
    {

        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 1
        ]));
        $result->isValid();
        $this->routeMatch->setParam('action', 'update');
        $this->routeMatch->setParam('id', 'name1');

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(new Parameters(array()));

        $result   = $this->controller->dispatch($request);
        $response = $this->controller->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testAdminCanUpdateAndTheFormIsTotallyValid()
    {

        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 1
        ]));
        $result->isValid();
        $this->routeMatch->setParam('action', 'update');
        $this->routeMatch->setParam('id', 'name1');

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(new Parameters($this->getValidPost()));

        $result   = $this->controller->dispatch($request);
        $msg = null;
        if ($result && isset($result->form)) {
            /** @var Group $form */
            $form = $result->form;
            $messages = $form->getMessages();
            $msg = $this->messages($messages);
        }
        $response = $this->controller->getResponse();
        $this->assertEquals(302, $response->getStatusCode(), $msg);
    }

    public function testAdminCanUpdateAndThisIsNoAdmin()
    {

        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 2
        ]));
        $result->isValid();
        $this->routeMatch->setParam('action', 'update');
        $this->routeMatch->setParam('id', 'name1');

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(new Parameters(array()));

        $result   = $this->controller->dispatch($request);
        $response = $this->controller->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testUpdateUserIsLoggedInButNotAMember()
    {
        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 3
        ]));
        $result->isValid();
        $this->routeMatch->setParam('action', 'update');
        $this->routeMatch->setParam('id', 'name1');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testUpdateUserIsLoggedAndIsAMember()
    {
        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 4
        ]));
        $result->isValid();
        $this->routeMatch->setParam('action', 'update');
        $this->routeMatch->setParam('id', 'name1');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testUpdateUserIsLoggedAndIsAManager()
    {
        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 5
        ]));
        $result->isValid();
        $this->routeMatch->setParam('action', 'update');
        $this->routeMatch->setParam('id', 'name1');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testUpdateUserIsLoggedAndIsAChairman()
    {
        $auth = new AuthenticationService();
        $result = $auth->authenticate(new TestAdapter((object)[
            'id' => 6
        ]));
        $result->isValid();
        $this->routeMatch->setParam('action', 'update');
        $this->routeMatch->setParam('id', 'name1');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     * Performs operation returned by getSetUpOperation().
     */
    protected function setUp()
    {

        $serviceManager = Bootstrap::getServiceManager();
        $this->controller = new GroupController();
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

        $serviceManager->setService('PDO', self::$pdo);
    }

    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {

        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO(
                    $GLOBALS['DB_DSN'],
                    $GLOBALS['DB_USER'],
                    $GLOBALS['DB_PASSWD'],
                    array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    )
                );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo);
        }

        return $this->conn;
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
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
            'User' => [
                ['id'=>1, 'passwd'=> '', 'is_admin' => 1, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
                ['id'=>2, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
                ['id'=>3, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
                ['id'=>4, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
                ['id'=>5, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
                ['id'=>6, 'passwd'=> '', 'is_admin' => 0, 'created_date'=> date('Y-m-d H:i:s'), 'modified_date'=> date('Y-m-d H:i:s')],
            ],
            'Group' => [
                ['name' =>'name1','name_short'=>'nameshort1','url'=>'name1']
            ],
            'Group_has_User' => [
                ['group_id'=>1, 'user_id'=>4, 'type'=>0],
                ['group_id'=>1, 'user_id'=>5, 'type'=>1],
                ['group_id'=>1, 'user_id'=>6, 'type'=>2],
            ],
        ]);
    }
}
