<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 12/11/14
 * Time: 14:12
 */

namespace Stjornvisi\Form;

class TestEvent extends \PHPUnit_Framework_TestCase
{
    public function testJustRequiredFields()
    {
        $form = new Event();
        $form->setData([
            'subject' => 'subject1',
            'event_date' => '2014-01-01',
            'event_time' => '12:00',
            'event_end' => '13:00'
        ]);
        $this->assertTrue($form->isValid(), print_r($form->getMessages(), true));
    }

    public function testEventEndsBeforeHeBegins()
    {
        $form = new Event();
        $form->setData([
            'subject' => 'subject1',
            'event_date' => '2014-01-01',
            'event_time' => '13:01',
            'event_end' => '13:00'
        ]);
        $this->assertFalse($form->isValid(), print_r($form->getMessages(), true));
    }

    public function testNotAValidDateFormat()
    {
        $form = new Event();
        $form->setData([
            'subject' => 'subject1',
            'event_date' => '13/11/2014',
            'event_time' => '13:00',
            'event_end' => '13:01'
        ]);
        $this->assertFalse($form->isValid(), print_r($form->getMessages(), true));
    }

    public function testNotAValidBeginTime()
    {
        $form = new Event();
        $form->setData([
            'subject' => 'subject1',
            'event_date' => '2014-01-01',
            'event_time' => '13-00',
            'event_end' => '13:01'
        ]);
        $this->assertFalse($form->isValid(), print_r($form->getMessages(), true));
    }

    public function testNotAValidEndTime()
    {
        $form = new Event();
        $form->setData([
            'subject' => 'subject1',
            'event_date' => '2014-01-01',
            'event_time' => '13:00',
            'event_end' => '13-01'
        ]);
        $this->assertFalse($form->isValid(), print_r($form->getMessages(), true));
    }

    public function testNotIntInCapacity()
    {
        $form = new Event();
        $form->setData([
            'subject' => 'subject1',
            'event_date' => '2014-01-01',
            'event_time' => '13:00',
            'event_end' => '13:01',
            'capacity' => 'hundur'
        ]);
        $this->assertFalse($form->isValid(), print_r($form->getMessages(), true));
    }

    public function testIntInCapacity()
    {
        $form = new Event();
        $form->setData([
            'subject' => 'subject1',
            'event_date' => '2014-01-01',
            'event_time' => '13:00',
            'event_end' => '13:01',
            'capacity' => '1234'
        ]);
        $this->assertTrue($form->isValid(), print_r($form->getMessages(), true));
    }

    public function testNegativeIntInCapacity()
    {
        $form = new Event();
        $form->setData([
            'subject' => 'subject1',
            'event_date' => '2014-01-01',
            'event_time' => '13:00',
            'event_end' => '13:01',
            'capacity' => -1
        ]);
        $this->assertTrue($form->isValid(), print_r($form->getMessages(), true));
    }

    public function testAddGroupsToTheMix()
    {
        $form = new Event();
        $form->setData([
            'subject' => 'subject1',
            'event_date' => '2014-01-01',
            'event_time' => '13:00',
            'event_end' => '13:01'
        ]);
        $form->bind(new \ArrayObject(['groups' => [(object)['id'=>1, 'name_short'=>'01']]]));
        $this->assertTrue($form->isValid(), print_r($form->getMessages(), true));
    }
}
