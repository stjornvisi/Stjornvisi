<?php

/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 1/08/15
 * Time: 6:56 PM
 */

namespace Stjornvisi\Properties;

use PHPUnit_Framework_TestCase;

class FilePropertiesTest extends PHPUnit_Framework_TestCase
{
    public function testReturnsObject()
    {
        $properties = new FileProperties('this-is-the-name.jpg');
        $result = json_decode(json_encode($properties));
        $this->assertInternalType('object', $result);
    }

    public function testObjectAttributes()
    {
        $properties = new FileProperties('this-is-the-name.jpg');
        $result = json_decode(json_encode($properties));

        $this->assertObjectHasAttribute('name', $result);
        $this->assertObjectHasAttribute('thumb', $result);
        $this->assertObjectHasAttribute('medium', $result);
        $this->assertObjectHasAttribute('large', $result);
        $this->assertObjectHasAttribute('original', $result);
        $this->assertObjectHasAttribute('raw', $result);
    }

    public function testFilePaths()
    {
        $properties = new FileProperties('this-is-the-name.jpg');
        $result = json_decode(json_encode($properties));

        $this->assertObjectHasAttribute('1x', $result->thumb);
        $this->assertObjectHasAttribute('2x', $result->thumb);

        $this->assertObjectHasAttribute('1x', $result->medium);
        $this->assertObjectHasAttribute('2x', $result->medium);

        $this->assertObjectHasAttribute('1x', $result->large);
        $this->assertObjectHasAttribute('2x', $result->large);

        $this->assertObjectHasAttribute('1x', $result->original);
        $this->assertObjectHasAttribute('2x', $result->original);
    }

    public function testFilePathGeneration()
    {
        $properties = new FileProperties('this-is-the-name.jpg');
        $result = json_decode(json_encode($properties));

        $this->assertEquals('/images/small/1x@this-is-the-name.jpg', $result->thumb->{'1x'});
        $this->assertEquals('/images/small/2x@this-is-the-name.jpg', $result->thumb->{'2x'});

        $this->assertEquals('/images/medium/1x@this-is-the-name.jpg', $result->medium->{'1x'});
        $this->assertEquals('/images/medium/2x@this-is-the-name.jpg', $result->medium->{'2x'});

        $this->assertEquals('/images/large/1x@this-is-the-name.jpg', $result->large->{'1x'});
        $this->assertEquals('/images/large/2x@this-is-the-name.jpg', $result->large->{'2x'});

        $this->assertEquals('/images/original/1x@this-is-the-name.jpg', $result->original->{'1x'});
        $this->assertEquals('/images/original/2x@this-is-the-name.jpg', $result->original->{'2x'});

        $this->assertEquals('/images/raw/this-is-the-name.jpg', $result->raw);
    }

    public function testFilePathPostFix()
    {
        $properties = (new FileProperties('this-is-the-name.jpg'))->setPostfix('.jpg');
        $result = json_decode(json_encode($properties));

        $this->assertEquals('/images/small/1x@this-is-the-name.jpg.jpg', $result->thumb->{'1x'});
    }
}
