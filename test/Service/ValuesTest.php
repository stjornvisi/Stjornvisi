<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 5/04/15
 * Time: 11:02 AM
 */

namespace Stjornvisi\Service;

use PHPUnit_Framework_TestCase;
use Zend\EventManager\EventManager;

class ValuesTest extends PHPUnit_Framework_TestCase
{
	public function testCreate()
	{
		$service = new Values();
		$this->assertInternalType('array', $service->getBusinessTypes());
		$this->assertInternalType('array', $service->getPostalCode());
		$this->assertInternalType('array', $service->getCompanySizes());
		$this->assertInternalType('array', $service->getTitles());


		$eventManager = new EventManager();

		$this->assertNotEquals($eventManager, $service->getEventManager());

		$service->setEventManager($eventManager);
		$this->assertEquals($eventManager, $service->getEventManager());
	}

	/**
	 *
	 */
	protected function setUp()
	{
		parent::setUp();
	}
}
