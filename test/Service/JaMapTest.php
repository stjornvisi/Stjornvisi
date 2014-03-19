<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/9/14
 * Time: 2:59 PM
 */

namespace Stjornvisi\Service;
use Zend\Http\Client;
use \PHPUnit_Framework_TestCase;

class JaMapTest extends PHPUnit_Framework_TestCase{

    public function testOne(){
        $map = new JaMap( new Client() );
        $result1 = $map->request("Hringbraut 107, 101 Reykjavík");
        $this->assertEquals( 64 , (int)$result1->lat );
        $this->assertEquals( -21, (int)$result1->lng );

        $result2 = $map->request("");
        $this->assertEquals( null , $result2->lat );
        $this->assertEquals( null, $result2->lng );
    }
} 