<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 17/02/15
 * Time: 14:58
 */

namespace Stjornvisi\Mail;


use Zend\Mail\Message;

class AttacherTest extends \PHPUnit_Framework_TestCase {

	public function testTrue(){



		$message = new Message();
		$message->setSubject('HUndur')
			->setBody( file_get_contents(__DIR__ . '/mail.test.01.txt') )
			->addFrom('ble@bla.is','ble')
			->addTo('hundur@vei.is','hundur');


		$attacher = new Attacher($message);
		$result = $attacher->parse();
		echo $result->toString();


	}
} 