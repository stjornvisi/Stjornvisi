<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 2/8/14
 * Time: 8:40 PM
 */

namespace Stjornvisi\Service;

use Zend\Http\Client;
use \PHPUnit_Framework_TestCase;

class GoogleMapTest extends PHPUnit_Framework_TestCase {

    public function testOne(){
        $map = new GoogleMap( new Client() );
        $result1 = $map->request("Hringbraut 107, 101 Reykjavík");
        $this->assertEquals( 64 , (int)$result1->lat );
        $this->assertEquals( -21, (int)$result1->lng );

        $result2 = $map->request("");
        $this->assertEquals( null , $result2->lat );
        $this->assertEquals( null, $result2->lng );
    }
} 