<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/11/14
 * Time: 16:11
 */

namespace Stjornvisi\Controller;

use Stjornvisi\Bootstrap;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;

class IndexControllerTest extends \PHPUnit_Framework_TestCase
{

    protected static $pdo;
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    protected function setUp()
    {

        $serviceManager = Bootstrap::getServiceManager();
        $this->controller = new IndexController();
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'index'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);
        $this->getConnection();
        $serviceManager->setService('PDO', self::$pdo);
    }


    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
            if (self::$pdo == null) {
                self::$pdo = new \PDO(
                    $GLOBALS['DB_DSN'],
                    $GLOBALS['DB_USER'],
                    $GLOBALS['DB_PASSWD'],
                    [
                        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                    ]
                );
            }
    }
    public function testIndexActionCanBeAccessed()
    {
        $this->routeMatch->setParam('action', 'index');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
