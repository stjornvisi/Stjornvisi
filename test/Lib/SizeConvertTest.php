<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 02/02/15
 * Time: 10:38
 */

namespace Stjornvisi\Lib;

use PHPUnit_Framework_TestCase;

class SizeConvertTest extends PHPUnit_Framework_TestCase {


	public function  dataProvider(){
		return [
			['2',	2],
			['102',	102],
			['2k',	2048],
			['2kb',	2048],
			['1m', 	1048576],
			['2M', 	2097152],
			['2MB', 2097152],
			['80mb', 83886080],
			['1g', 	1073741824],
			['2G', 	2147483648],
			['3gB', 3221225472],
			['hundur', 0],
			['3hundur', 3],
			['hundur3', 0],
			['3hundur3', 3],
			[false, 0],
			[null, 0],
			[true, 0],
		];
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testConvert( $in, $out ){




		$sut = new SizeConvert();

		$this->assertEquals( $out, $sut->convert( $in ) );

	}
} 