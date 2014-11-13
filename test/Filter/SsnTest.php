<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 10/11/14
 * Time: 12:36
 */

namespace Stjornvisi\Filter;


class SsnTest extends \PHPUnit_Framework_TestCase {


	public function dataProvider(){
		return array(
			array('1234','1234'),
			array('1234T','1234'),
			array('110478-4960','1104784960'),
			array('Einar2Valur','2'),
			array('Einar 2 Valur','2'),
		);
	}
	/**
	 * @param $value
	 * @param $result
	 * @dataProvider dataProvider
	 */
	public function testInput( $value, $result ){
		$filter = new Ssn();
		$this->assertEquals( $result, $filter->filter( $value ) );
	}
} 