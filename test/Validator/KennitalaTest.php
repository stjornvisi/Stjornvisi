<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 08/11/14
 * Time: 21:18
 */

namespace Stjornvisi\Validator;

class KennitalaTest extends \PHPUnit_Framework_TestCase
{
	public function dataProvider()
	{
		return [
			[ '1104784969', true ],
			[ '1007092870', true ],
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testSsnValidation($number, $boolean)
	{
		$validator = new Kennitala();
		$this->assertEquals($boolean, $validator->isValid($number));
	}
}