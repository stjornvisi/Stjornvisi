<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 18/04/15
 * Time: 4:29 PM
 */

namespace Stjornvisi\View\Helper;

use \PHPUnit_Framework_TestCase;

class DateTest extends PHPUnit_Framework_TestCase
{
    public function testNotDateType()
    {
        $this->assertEquals(' 1. janúar 2000', (new Date())->__invoke(new \DateTime('2000-01-01')));
        $this->assertEquals('', (new Date())->__invoke('not a date object'));
    }
}
