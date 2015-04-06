<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/8/14
 * Time: 8:40 PM
 */

namespace Stjornvisi\Service;

use Zend\Http\Client;
use Zend\Http\Client\Adapter\Test;
use \PHPUnit_Framework_TestCase;

class GoogleMapTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Everything works
	 */
	public function testSuccess()
	{
		$client = new Client();
		$adapter = new Test();
		$adapter->setResponse(file_get_contents(__DIR__.'/../data/google-map-response/01.txt'));
		$client->setAdapter($adapter);

		$map = new GoogleMap($client);
		$result = $map->request('My address');
		$this->assertTrue(is_float($result->lat));
		$this->assertTrue(is_float($result->lng));
	}

	/**
	 * Not a valid JSON string in result
	 */
	public function testInvalidResponse()
	{
		$client = new Client();
		$adapter = new Test();
		$adapter->setResponse(file_get_contents(__DIR__.'/../data/google-map-response/02.txt'));
		$client->setAdapter($adapter);

		$map = new GoogleMap($client);
		$result = $map->request('My address');
		$this->assertNull($result->lat);
		$this->assertNull($result->lng);
	}

	/**
	 * Result is not HTTP/1.1 200
	 */
	public function testHttpError()
	{
		$client = new Client();
		$adapter = new Test();
		$adapter->setResponse(file_get_contents(__DIR__.'/../data/google-map-response/03.txt'));
		$client->setAdapter($adapter);

		$map = new GoogleMap($client);
		$result = $map->request('My address');
		$this->assertNull($result->lat);
		$this->assertNull($result->lng);
	}
}
