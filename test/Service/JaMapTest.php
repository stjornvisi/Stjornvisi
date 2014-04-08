<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/9/14
 * Time: 2:59 PM
 */

namespace Stjornvisi\Service;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Test;
use \PHPUnit_Framework_TestCase;

class JaMapTest extends PHPUnit_Framework_TestCase{

	/**
	 * Everything should work
	 */
	public function testSuccess(){
		$client = new Client();
		$adapter = new Test();
		$adapter->setResponse( file_get_contents(__DIR__.'/../data/ja-response/01.txt') );
		$client->setAdapter($adapter);
		$map = new JaMap( $client );
		$result1 = $map->request("Hringbraut 107, 101 Reykjavík");
		$this->assertEquals( 64 , (int)$result1->lat );
		$this->assertEquals( -21, (int)$result1->lng );
	}

	/**
	 * Result is not complete, there is missing the last part
	 * of the json string
	 */
	public function testUnexpectedEndOfResult(){
		$client = new Client();
		$adapter = new Test();
		$adapter->setResponse( file_get_contents(__DIR__.'/../data/ja-response/02.txt') );
		$client->setAdapter($adapter);
		$map = new JaMap( $client );
		$result1 = $map->request("Hringbraut 107, 101 Reykjavík");
		$this->assertNull( $result1->lat );
		$this->assertNull( $result1->lng );
	}

	/**
	 * Data is OK, but the response header
	 * is HTTP/1.1 404 Not Found
	 */
	public function testNot200ResponseCodet(){
		$client = new Client();
		$adapter = new Test();
		$adapter->setResponse( file_get_contents(__DIR__.'/../data/ja-response/03.txt') );
		$client->setAdapter($adapter);
		$map = new JaMap( $client );
		$result1 = $map->request("Hringbraut 107, 101 Reykjavík");
		$this->assertNull( $result1->lat );
		$this->assertNull( $result1->lng );
	}
} 
