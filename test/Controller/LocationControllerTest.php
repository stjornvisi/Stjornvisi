<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/11/14
 * Time: 16:11
 */

namespace Stjornvisi\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class LocationControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ .'/../application.config.php'
        );
        parent::setUp();
    }

    public function testConvertLocationSuccess()
    {
        $this->registerUserService([]);
        $this->registerLocationService([]);

        $this->dispatch('/location', 'GET', [
            'q' => 'some street'
        ]);

        $this->assertControllerClass('LocationController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);
        $this->assertNotResponseHeaderContains('Content-type', 'application/json');

    }

    private function registerLocationService($response)
    {
        $locationServiceMock = \Mockery::mock('Stjornvisi\Service\Map\JaMap')
            ->shouldReceive('request')
            ->andReturn($response)
            ->getMock();

        $this->getApplicationServiceLocator()
            ->setAllowOverride(true)
            ->setService('Stjornvisi\Service\Map', $locationServiceMock);
    }

    /**
     * Create a mock User Service.
     * @param object $userObject Record from 'getTypeByGroup'
     */
    private function registerUserService($userObject)
    {
        $userServiceMock = \Mockery::mock('Stjornvisi\Service\User')
            ->shouldReceive('getTypeByGroup')
            ->andReturn($userObject)
            ->getMock()
            ->shouldReceive('getByHash')
            ->andReturn($userObject)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\User', $userServiceMock);
    }
}
