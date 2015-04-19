<?php
/**
 * Created by PhpStorm.
 * User: einarvalur
 * Date: 9/04/15
 * Time: 6:13 AM
 */

namespace Stjornvisi\Notify\Message;

use PHPUnit_Framework_TestCase;

class MailTest extends PHPUnit_Framework_TestCase
{
    public function testSetProperties()
    {
        $message = new Mail();
        $message->name = 'Hundur';

        $this->assertEquals('Hundur', $message->name);
    }

    public function testSetUndefinedProperty()
    {
        $message = new Mail();
        $message->undefind = 'Hundur';

        $this->assertNull($message->undefind);
    }

    public function testAfterSerialization()
    {
        $messageGetter = new Mail();
        $messageGetter->email = 'myemail@somemail.com';
        $messageGetter->test = false;

        $serialized = $messageGetter->serialize();

        $messageSetter = new Mail();
        $messageSetter->unserialize($serialized);

        $this->assertEquals('myemail@somemail.com', $messageSetter->email);
        $this->assertFalse($messageSetter->test);
    }
}
