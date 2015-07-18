<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 10/07/15
 * Time: 9:01 AM
 */

namespace Stjornvisi\Controller;

use DateTime;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class EventControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ .'/../application.config.php'
        );
        parent::setUp();
    }

    public function testGetEntryNotFound()
    {
        $modifiedEventData = null;

        $this->registerAdminSession();
        $this->registerEventService($modifiedEventData);
        $this->registerGroupService();

        $this->dispatch('/vidburdir/707', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('not-found');
        $this->assertResponseStatusCode(404);
    }

    public function testPreEventOutOfCapacityLoggedInUserIsAttendingCanUnRegister()
    {
        $modifiedEventData = [
            'event_date' => (new DateTime())->add(new \DateInterval('PT1M')),
            'capacity' => 2,
            'attending' => true,
            'attenders' => array_fill(0, 2, (object) [
                'user_id' => 1,
                'register_time' => new DateTime(),
                'name'=> 'n1',
                'title'=> 't1',
                'company_name' => 'c1',
                'company_id' => 1]),
        ];

        $this->registerAdminSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData($modifiedEventData));

        $this->dispatch('/vidburdir/707', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);

        $this->assertQueryCount('.page-event__register', 0);
        $this->assertQueryCount('span.attending.yes', 1);
        $this->assertQueryCount('a.attending.no', 1);
        $this->assertNotQueryContentContains('.page-event-element__attenders h3', 'Viðburður liðinn.');
        $this->assertQueryContentContains('.page-event-element__attenders h3', 'Viðburður er fullur.');
        $this->assertQueryCount('stjonvisi-control', 1);
    }

    public function testPreEventOutOfCapacityLoggedInUserIsNotAttendingCanNotRegister()
    {
        $modifiedEventData = [
            'event_date' => (new DateTime())->add(new \DateInterval('PT1M')),
            'capacity' => 2,
            'attending' => false,
            'attenders' => array_fill(0, 2, (object) [
                'user_id' => 1,
                'register_time' => new DateTime(),
                'name'=> 'n1',
                'title'=> 't1',
                'company_name' => 'c1',
                'company_id' => 1]),
        ];

        $this->registerAdminSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData($modifiedEventData));

        $this->dispatch('/vidburdir/707', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);

        $this->assertQueryCount('.page-event__register', 0);
        $this->assertQueryCount('.attending.yes', 0);
        $this->assertQueryCount('.attending.no', 0);
        $this->assertNotQueryContentContains('.page-event-element__attenders h3', 'Viðburður liðinn.');
        $this->assertQueryContentContains('.page-event-element__attenders h3', 'Viðburður er fullur.');
        $this->assertQueryCount('stjonvisi-control', 1);
    }

    public function testPreEventNotLoggedInWillGetRegisterForm()
    {
        $modifiedEventData = [
            'event_date' => (new DateTime())->add(new \DateInterval('PT1M')),
            'capacity' => 4,
            'attending' => false,
            'attenders' => array_fill(0, 2, (object) [
                'user_id' => 1,
                'register_time' => new DateTime(),
                'name'=> 'n1',
                'title'=> 't1',
                'company_name' => 'c1',
                'company_id' => 1]),
        ];

        $this->registerAnonymousSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData($modifiedEventData));

        $this->dispatch('/vidburdir/707', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);

        $this->assertQueryCount('.page-event__register', 1);
        $this->assertQueryCount('.attending.yes', 0);
        $this->assertQueryCount('.attending.no', 0);
        $this->assertQueryCount('stjonvisi-control', 0);
    }

    public function testPreEventLoggedInWillGetButtons()
    {
        $modifiedEventData = [
            'event_date' => (new DateTime())->add(new \DateInterval('PT1M')),
            'capacity' => 4,
            'attending' => false,
            'attenders' => array_fill(0, 2, (object) [
                'user_id' => 1,
                'register_time' => new DateTime(),
                'name'=> 'n1',
                'title'=> 't1',
                'company_name' => 'c1',
                'company_id' => 1]),
        ];

        $this->registerUserSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData($modifiedEventData));

        $this->dispatch('/vidburdir/707', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);

        $this->assertQueryCount('.page-event__register', 0);
        $this->assertQueryCount('.attending.yes', 1);
        $this->assertQueryCount('.attending.no', 1);
        $this->assertQueryCount('stjonvisi-control', 0);
    }

    public function testPreEventOutOfCapacityNotLoggedInCanDoNothing()
    {
        $modifiedEventData = [
            'event_date' => (new DateTime())->add(new \DateInterval('PT1M')),
            'capacity' => 2,
            'attending' => true,
            'attenders' => array_fill(0, 2, (object) [
                'user_id' => 1,
                'register_time' => new DateTime(),
                'name'=> 'n1',
                'title'=> 't1',
                'company_name' => 'c1',
                'company_id' => 1]),''
        ];

        $this->registerAnonymousSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData($modifiedEventData));

        $this->dispatch('/vidburdir/707', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);

        $this->assertQueryCount('.page-event__register', 0);
        $this->assertQueryCount('.attending.yes', 0);
        $this->assertQueryCount('.attending.no', 0);
        $this->assertNotQueryContentContains('.page-event-element__attenders h3', 'Viðburður liðinn.');
        $this->assertQueryContentContains('.page-event-element__attenders h3', 'Viðburður er fullur.');
        $this->assertQueryCount('stjonvisi-control', 0);
    }

    public function testPostEventOutOfCapacity()
    {
        $modifiedEventData = [
            'event_date' => (new DateTime())->sub(new \DateInterval('PT1M')),
            'capacity' => 2,
            'attending' => true,
            'attenders' => array_fill(0, 2, (object) [
                'user_id' => 1,
                'register_time' => new DateTime(),
                'name'=> 'n1',
                'title'=> 't1',
                'company_name' => 'c1',
                'company_id' => 1]),
        ];

        $this->registerAnonymousSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData($modifiedEventData));

        $this->dispatch('/vidburdir/707', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);

        $this->assertQueryCount('.page-event__register', 0);
        $this->assertQueryCount('.attending.yes', 0);
        $this->assertQueryCount('.attending.no', 0);
        $this->assertQueryContentContains('.page-event-element__attenders h3', 'Viðburður liðinn.');
        $this->assertNotQueryContentContains('.page-event-element__attenders h3', 'Viðburður er fullur.');
        $this->assertQueryCount('stjonvisi-control', 0);
    }

    public function testPostEventUserNotLoggedIn()
    {
        $modifiedEventData = [
            'event_date' => (new DateTime())->sub(new \DateInterval('PT1M')),
            'capacity' => 2,
            'attending' => true,
            'attenders' => array_fill(0, 2, (object) [
                'user_id' => 1,
                'register_time' => new DateTime(),
                'name'=> 'n1',
                'title'=> 't1',
                'company_name' => 'c1',
                'company_id' => 1]),
        ];

        $this->registerAnonymousSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData($modifiedEventData));

        $this->dispatch('/vidburdir/707', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);

        $this->assertQueryCount('.page-event__register', 0);
        $this->assertQueryCount('.attending.yes', 0);
        $this->assertQueryCount('.attending.no', 0);
        $this->assertQueryContentContains('.page-event-element__attenders h3', 'Viðburður liðinn.');
        $this->assertNotQueryContentContains('.page-event-element__attenders h3', 'Viðburður er fullur.');
        $this->assertQueryCount('stjonvisi-control', 0);
    }

    public function testPostEventUserLoggedIn()
    {
        $modifiedEventData = [
            'event_date' => (new DateTime())->sub(new \DateInterval('PT1M')),
            'capacity' => 2,
            'attending' => true,
            'attenders' => array_fill(0, 2, (object) [
                'user_id' => 1,
                'register_time' => new DateTime(),
                'name'=> 'n1',
                'title'=> 't1',
                'company_name' => 'c1',
                'company_id' => 1]),
        ];

        $this->registerUserSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData($modifiedEventData));

        $this->dispatch('/vidburdir/707', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);

        $this->assertQueryCount('.page-event__register', 0);
        $this->assertQueryCount('.attending.yes', 0);
        $this->assertQueryCount('.attending.no', 0);
        $this->assertQueryContentContains('.page-event-element__attenders h3', 'Viðburður liðinn.');
        $this->assertNotQueryContentContains('.page-event-element__attenders h3', 'Viðburður er fullur.');
        $this->assertQueryCount('stjonvisi-control', 0);
    }

    public function testPostRequestWillRegisterUser()
    {
        $this->registerAnonymousSession();
        $this->registerGroupService();

        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('get')
            ->once()
            ->andReturn($this->getEventDatabaseMockData())
            ->getMock()
            ->shouldReceive('getRelated')
            ->andReturn([])
            ->getMock()
            ->shouldReceive('aggregateAttendance')
            ->andReturn([])
            ->getMock()
            ->shouldReceive('registerUser')
            ->once()
            ->andReturnNull()
            ->getMock()
        ;
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir/1', 'POST', [
            'name' => 'name one',
            'email' => 'name@one.com',
        ]);
        $this->assertControllerClass('EventController');
        $this->assertActionName('index');
        $this->assertResponseStatusCode(200);
    }

    public function testListActionSuccess()
    {
        $this->registerAnonymousSession();

        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('getRange')
            ->andReturn([$this->getEventDatabaseMockData()])
            ->getMock();

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir');
        $this->assertControllerClass('EventController');
        $this->assertActionName('list');
        $this->assertResponseStatusCode(200);
    }

    public function testCreateAccessDenied()
    {
        $this->registerAnonymousSession();
        $this->registerGroupService();
        $this->registerEventService([]);

        $this->dispatch('/vidburdir/stofna', 'POST');
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertResponseStatusCode(401);
    }

    public function testCreateThisIsAGlobalEventSoOnlyAdminCanAccess()
    {
        $this->registerManagerSession();
        $this->registerGroupService();
        $this->registerEventService([]);

        $this->dispatch('/vidburdir/stofna', 'POST');
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertResponseStatusCode(401);
    }

    public function testCreateThisIsAGroupEventAndLoggedInUserCanNotAccess()
    {
        $this->registerUserSession();
        $this->registerGroupService();
        $this->registerEventService([]);

        $this->dispatch('/vidburdir/stofna/1', 'POST');
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertResponseStatusCode(401);
    }

    public function testCreateAdminWillTriggerCreateServiceCallOnGroupEvent()
    {
        $this->registerAdminSession();
        $this->registerGroupService();

        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('create')
            ->once()
            ->andReturn(123)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir/stofna/1', 'POST', [
            'subject' => 'Some Subject',
            'event_date' => '2000-01-01',
            'event_time' => '20:00',
            'event_end' => '21:00',
        ]);
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertRedirectTo('/vidburdir/123');
    }

    public function testCreateAdminWillTriggerCreateServiceCallOnGlobalEvent()
    {
        $this->registerAdminSession();
        $this->registerGroupService();

        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('create')
            ->once()
            ->andReturn(123)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir/stofna', 'POST', [
            'subject' => 'Some Subject',
            'event_date' => '2000-01-01',
            'event_time' => '20:00',
            'event_end' => '21:00',
        ]);
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertRedirectTo('/vidburdir/123');
    }

    public function testCreateManagerWillTriggerCreateServiceCallOnGroupEvent()
    {
        $this->registerManagerSession();
        $this->registerGroupService();

        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('create')
            ->once()
            ->andReturn(123)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir/stofna/1', 'POST', [
            'subject' => 'Some Subject',
            'event_date' => '2000-01-01',
            'event_time' => '20:00',
            'event_end' => '21:00',
        ]);
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertRedirectTo('/vidburdir/123');
    }

    public function testCreateManagerWillNotTriggerCreateServiceCallOnGlobalEvent()
    {
        $this->registerManagerSession();
        $this->registerGroupService();

        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('create')
            ->never()
            ->andReturn(123)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir/stofna', 'POST', [
            'subject' => 'Some Subject',
            'event_date' => '2000-01-01',
            'event_time' => '20:00',
            'event_end' => '21:00',
        ]);
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertResponseStatusCode(401);
    }

    public function testCreateAdminWillGetInvalidFormError()
    {
        $this->registerAdminSession();
        $this->registerGroupService();

        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('create')
            ->never()
            ->andReturn(123)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir/stofna/1', 'POST', [
            'subject' => 'Some Subject',
            'event_date' => 'Invalid Date',
        ]);
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertResponseStatusCode(400);
    }

    public function testCreateManagerWillGetInvalidFormError()
    {
        $this->registerManagerSession();
        $this->registerGroupService();

        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('create')
            ->never()
            ->andReturn(123)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir/stofna/1', 'POST', [
            'subject' => 'Some Subject',
            'event_date' => 'Invalid Date',
        ]);
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertResponseStatusCode(400);
    }

    public function testCreateAdminWillGetFormOnGETRequestOnGlobalEvent()
    {
        $this->registerAdminSession();
        $this->registerGroupService();
        $this->registerEventService([]);

        $this->dispatch('/vidburdir/stofna', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertResponseStatusCode(200);
    }

    public function testCreateManagerWillNotGetFormOnGETRequestOnGlobalEvent()
    {
        $this->registerManagerSession();
        $this->registerGroupService();
        $this->registerEventService([]);

        $this->dispatch('/vidburdir/stofna', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertResponseStatusCode(401);
    }

    public function testCreateManagerWillGetFormOnGETRequestOnGroupEvent()
    {
        $this->registerManagerSession();
        $this->registerGroupService();
        $this->registerEventService([]);

        $this->dispatch('/vidburdir/stofna/1', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('create');
        $this->assertResponseStatusCode(200);
    }

    public function testUpdateEventNotFoundGET()
    {
        $this->registerAdminSession();
        $this->registerGroupService();
        $this->registerEventService(null);

        $this->dispatch('/vidburdir/1/uppfaera', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('not-found');
        $this->assertResponseStatusCode(404);
    }

    public function testUpdateUserCanNotUpdateEventGET()
    {
        $this->registerUserSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData());

        $this->dispatch('/vidburdir/1/uppfaera', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('update');
        $this->assertResponseStatusCode(401);
    }

    public function testUpdateManagerGetsFormGET()
    {
        $this->registerManagerSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData());

        $this->dispatch('/vidburdir/1/uppfaera', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('update');
        $this->assertResponseStatusCode(200);
    }

    public function testUpdateManagerWillGetErrorWithInvalidForm()
    {
        $this->registerManagerSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData());

        $this->dispatch('/vidburdir/1/uppfaera', 'POST', [
            'event_date' => 'Invalid Date'
        ]);
        $this->assertControllerClass('EventController');
        $this->assertActionName('update');
        $this->assertResponseStatusCode(400);
    }

    public function testUpdateManagerWillTriggerUpdateOnService()
    {
        $this->registerManagerSession();
        $this->registerGroupService();
        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('get')
            ->andReturn($this->getEventDatabaseMockData(['id' => 123]))
            ->shouldReceive('update')
            ->once()
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir/1/uppfaera', 'POST', [
            'subject' => 'Some Subject',
            'event_date' => '2000-01-01',
            'event_time' => '20:00',
            'event_end' => '21:00',
        ]);
        $this->assertControllerClass('EventController');
        $this->assertActionName('update');
        $this->assertRedirectTo('/vidburdir/123');
    }

    public function testDeleteEventNotFound()
    {
        $this->registerManagerSession();
        $this->registerGroupService();
        $this->registerEventService(null);

        $this->dispatch('/vidburdir/1/eyda', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('not-found');
        $this->assertResponseStatusCode(404);
    }

    public function testDeleteRegularUserWillNotAccess()
    {
        $this->registerUserSession();
        $this->registerGroupService();
        $this->registerEventService($this->getEventDatabaseMockData());

        $this->dispatch('/vidburdir/1/eyda', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('delete');
        $this->assertResponseStatusCode(401);
    }

    public function testDeleteManagerWillTriggerServiceDelete()
    {
        $this->registerManagerSession();
        $this->registerGroupService();
        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('get')
            ->andReturn($this->getEventDatabaseMockData())
            ->once()
            ->shouldReceive('delete')
            ->once()
            ->andReturn(123)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);

        $this->dispatch('/vidburdir/1/eyda', 'GET');
        $this->assertControllerClass('EventController');
        $this->assertActionName('delete');
        $this->assertRedirectTo('/vidburdir');
    }

    /**
     * Register a session where the user is Admin
     */
    private function registerAdminSession()
    {
        $this->registerAuthenticationService(true, (object) ['is_admin' => true, 'id' => 1]);
        $this->registerUserService((object) ['is_admin' => true, 'type' => null]);
    }

    /**
     * Register a session the user is not logged in
     */
    private function registerAnonymousSession()
    {
        $this->registerAuthenticationService(false, (object) ['is_admin' => false, 'id' => null]);
        $this->registerUserService((object) ['is_admin' => false, 'type' => null]);
    }


    /**
     * Register a session the user is not logged in
     */
    private function registerManagerSession()
    {
        $this->registerAuthenticationService(true, (object) ['is_admin' => false, 'id' => 1]);
        $this->registerUserService((object) ['is_admin' => false, 'type' => 2]);
    }

    /**
     * Register a session when a user is logged in
     * but has no access rights
     */
    private function registerUserSession()
    {
        $this->registerAuthenticationService(true, (object) ['is_admin' => false, 'id' => 1]);
        $this->registerUserService((object) ['is_admin' => false, 'type' => null]);
    }

    /**
     * Create a mock AuthenticationService service
     * @param bool $hasIdentity
     * @param object $userObject What is returned by 'getIdentity'
     */
    private function registerAuthenticationService($hasIdentity, $userObject)
    {
        $authServiceMock = \Mockery::mock('Zend\Authentication\AuthenticationService')
            ->shouldReceive('hasIdentity')
            ->andReturn($hasIdentity)
            ->getMock()
            ->shouldReceive('getIdentity')
            ->andReturn($userObject)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('AuthenticationService', $authServiceMock);
    }

    /**
     * Create a mock Event Service
     * @param object $data Database return record
     */
    private function registerEventService($data)
    {
        $eventServiceMock = \Mockery::mock('Stjornvisi\Service\Event')
            ->shouldReceive('get')
            ->once()
            ->andReturn($data)
            ->getMock()
            ->shouldReceive('getRelated')
            ->andReturn([])
            ->getMock()
            ->shouldReceive('aggregateAttendance')
            ->andReturn([])
            ->getMock()
            ->shouldReceive('fetchAll')
            ->andReturn($data)
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Event', $eventServiceMock);
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

    /**
     * Create a mock Group service
     */
    private function registerGroupService()
    {
        $groupServiceMock = \Mockery::mock('Stjornvisi\Service\Group')
            ->shouldReceive('getByUser')
            ->andReturn([])
            ->getMock()
            ->shouldReceive('fetchAll')
            ->andReturn([])
            ->getMock();
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Stjornvisi\Service\Group', $groupServiceMock);
    }

    /**
     * Create a Event Database record.
     * The event starts one minute after the test runs.
     *
     * Pass in additional data that will be merged with
     * the original data.
     *
     * @param array $data
     * @return object
     */
    private function getEventDatabaseMockData(array $data = [])
    {
        $originalData = [
            'id' => 1,
            'subject' => 's1',
            'body' => 'b1',
            'location' => 'l1',
            'address' => 'a1',
            'capacity' => 10,
            'event_date' => (new DateTime())->add(new \DateInterval('PT1M')),
            'event_time' => new DateTime(),
            'event_end' => new DateTime(),
            'avatar' => null,
            'lat' => 1,
            'lng' => 1,
            'groups' => [
                (object) [
                    'id' => 1,
                    'url' => 'some-url',
                    'name_short' => 'ns']
            ],
            'gallery' => [
                (object) [
                    'id' => 1,
                    'name' => 'some-name',
                    'description' => 'desc'
                ]
            ],
            'reference' => [
                (object) [
                    'id' => 1,
                    'name' => 'some-name',
                    'description' => 'desc'
                ]
            ],
            'attenders' => [],
            'attending' => true
        ];

        return (object) array_merge($originalData, $data);
    }
}
