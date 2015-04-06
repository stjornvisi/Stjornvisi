<?php
/**
 * Created by PhpStorm.
 * User: einar
 * Date: 02/02/15
 * Time: 10:37
 */

namespace Stjornvisi\Lib;

/**
 * Class SizeConvert
 * @package Stjornvisi\Lib
 */
class SizeConvert
{

	/**
	 * Convert a string of what ever size to bites.
	 *
	 * Example input:8mb will produce output:83886080
	 *
	 * @param $string
	 * @return int
	 */
	public function convert($string)
	{
		if (!is_string($string)) {
			return 0;
		}

		$matches = [];
		preg_match("/([0-9]*)([a-zA-Z]*)/", $string, $matches);
		if (count($matches) != 3) {
			return 0;
		}

		$value = (int)$matches[1];
		$units = strtolower($matches[2]);

		switch ($units) {
			case "k":
			case "kb":
				return $value*1024;
			case "m":
			case "mb":
				return $value*pow(1024, 2);
			case "g":
			case "gb":
				return $value*pow(1024, 3);
			case "t":
			case "tb":
				return $value*pow(1024, 4);
			case "p":
			case "pb":
				return $value*pow(1024, 5);
			default:
				return $value;
		}
	}
}
